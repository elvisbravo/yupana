<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Sincronización con la API WhatsApp local (Node):
 *  - webhook: mensajes y acks que Node envía apenas ocurren
 *  - outbox: mensajes escritos en Yupana que Node recoge y envía
 */
class WaSyncModel extends Model
{
    /** Minutos en 'enviando' tras los cuales se reintenta el envío. */
    private const MINUTOS_ENVIANDO = 2;

    private const MAX_INTENTOS = 3;

    private const ETIQUETAS_MEDIA = [
        'image'    => '[imagen]',
        'video'    => '[video]',
        'audio'    => '[audio]',
        'ptt'      => '[audio]',
        'document' => '[documento]',
        'sticker'  => '[sticker]',
        'location' => '[ubicación]',
        'vcard'    => '[contacto]',
    ];

    protected $table      = 'wa_mensajes';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    // ------------------------------------------------------------------ webhook

    /**
     * Guarda (o actualiza) un mensaje recibido por webhook.
     * Devuelve el id de wa_mensajes.
     */
    public function guardarMensaje(string $instancia, array $m): int
    {
        $ahora = date('Y-m-d H:i:s');
        $fecha = !empty($m['timestamp']) ? date('Y-m-d H:i:s', (int) $m['timestamp']) : $ahora;
        $fromMe = !empty($m['from_me']);
        $ack = isset($m['ack']) ? (int) $m['ack'] : 0;

        $this->db->transStart();

        // Copia de un envío hecho desde Yupana: se asocia a la fila de la outbox en vez de duplicarlo
        if ($fromMe && !empty($m['outbox_id'])) {
            $outboxId = $this->asociarEnvio($instancia, (int) $m['outbox_id'], $m['wa_message_id'], $m['wa_chat_id'], $ack);
            if ($outboxId) {
                $this->db->transComplete();

                return $outboxId;
            }
        }

        $chat = $this->obtenerOCrearChat($instancia, $m, $ahora);

        $existente = $this->db->table('wa_mensajes')
            ->select('id')
            ->where('instancia_id', $instancia)
            ->where('wa_message_id', $m['wa_message_id'])
            ->get()->getRow();

        if ($existente) {
            // Ya estaba (reintento del webhook, o enviado desde Yupana): solo se avanza el ack
            if ($ack > 0) {
                $this->aplicarAck($instancia, $m['wa_message_id'], $ack);
            }
            $this->db->transComplete();

            return (int) $existente->id;
        }

        $tieneMedia = !empty($m['tiene_media']) || !empty($m['media']);

        $this->db->table('wa_mensajes')->insert([
            'chat_id'       => $chat->id,
            'instancia_id'  => $instancia,
            'wa_message_id' => $m['wa_message_id'],
            'direccion'     => $fromMe ? 'saliente' : 'entrante',
            'origen'        => $fromMe ? 'celular' : 'whatsapp',
            'autor'         => $this->corto($m['autor'] ?? null, 100),
            'tipo'          => $this->corto($m['tipo'] ?? 'chat', 20) ?: 'chat',
            'cuerpo'        => ($m['cuerpo'] ?? '') !== '' ? $m['cuerpo'] : null,
            'tiene_media'   => $tieneMedia ? 1 : 0,
            'respuesta_a'   => $this->corto($m['respuesta_a'] ?? null, 191),
            'ack'           => $fromMe ? max(0, $ack) : 0,
            'fecha_mensaje' => $fecha,
            'created_at'    => $ahora,
            'updated_at'    => $ahora,
        ]);
        $mensajeId = (int) $this->db->insertID();

        if ($fromMe && $ack > 0) {
            $this->aplicarAck($instancia, $m['wa_message_id'], $ack);
        }

        if (!empty($m['media']['data'])) {
            $this->guardarMedia($instancia, $mensajeId, $m['media'], $ahora);
        }

        // Vista previa y contadores del chat (solo si el mensaje es el más reciente)
        $cambios = [];
        if ($chat->ultimo_mensaje_at === null || $fecha >= $chat->ultimo_mensaje_at) {
            $cambios['ultimo_mensaje'] = $this->vistaPrevia($m);
            $cambios['ultimo_mensaje_at'] = $fecha;
        }
        if (!$fromMe && $chat->estado_chat === 'cerrado') {
            $cambios['estado_chat'] = 'abierto';
            $this->registrarEvento((int) $chat->id, 'reabierto', $ahora);
        }
        // Un mensaje recuperado del historial, anterior a la última lectura del chat, no cuenta como no leído
        $sumarNoLeido = false;
        if (!$fromMe) {
            $lectura = $this->db->table('wa_chat_lecturas')->selectMax('leido_at')->where('chat_id', $chat->id)->get()->getRow();
            $sumarNoLeido = empty($lectura->leido_at) || $fecha > date('Y-m-d H:i:s', strtotime($lectura->leido_at) - 60);
        }
        $b = $this->db->table('wa_chats')->where('id', $chat->id);
        if ($sumarNoLeido) {
            $b->set('no_leidos', 'no_leidos + 1', false);
        }
        if ($cambios || $sumarNoLeido) {
            $b->set($cambios)->set('updated_at', $ahora)->update();
        }

        $this->db->transComplete();

        return $mensajeId;
    }

    /** El ack solo avanza; completa las horas de los estados intermedios que falten. */
    public function aplicarAck(string $instancia, string $waMessageId, int $ack): void
    {
        $ahora = date('Y-m-d H:i:s');

        $this->db->query(
            'UPDATE wa_mensajes SET
                ack          = IF(? < 0, IF(ack > 0, ack, ?), GREATEST(ack, ?)),
                enviado_at   = IF(? >= 1, COALESCE(enviado_at, ?), enviado_at),
                entregado_at = IF(? >= 2, COALESCE(entregado_at, ?), entregado_at),
                visto_at     = IF(? >= 3, COALESCE(visto_at, ?), visto_at),
                updated_at   = ?
             WHERE instancia_id = ? AND wa_message_id = ?',
            [$ack, $ack, $ack, $ack, $ahora, $ack, $ahora, $ack, $ahora, $ahora, $instancia, $waMessageId]
        );
    }

    public function latido(string $instancia, string $estado, ?string $numero): void
    {
        $datos = [
            'estado'             => $this->corto($estado, 20) ?: 'desconocido',
            'ultimo_contacto_at' => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ];
        if ($numero) {
            $datos['numero'] = $this->corto(preg_replace('/\D/', '', $numero), 20);
        }

        $this->db->table('wa_instancias')->where('id', $instancia)->update($datos);
    }

    public function instanciaExiste(string $instancia): bool
    {
        return $this->db->table('wa_instancias')->where('id', $instancia)->where('activo', 1)->countAllResults() > 0;
    }

    // ------------------------------------------------------------------ outbox

    /** Toma los mensajes pendientes y los marca como 'enviando'. */
    public function tomarSalientes(string $instancia, int $max = 10): array
    {
        $ahora = date('Y-m-d H:i:s');
        $limite = date('Y-m-d H:i:s', time() - self::MINUTOS_ENVIANDO * 60);

        $this->db->transStart();

        // Envíos que quedaron colgados (Node se cayó a mitad de envío)
        $this->db->table('wa_mensajes')
            ->where('instancia_id', $instancia)
            ->where('estado_envio', 'enviando')
            ->where('updated_at <', $limite)
            ->where('intentos >=', self::MAX_INTENTOS)
            ->update(['estado_envio' => 'error', 'error' => 'Sin confirmación de la API local', 'updated_at' => $ahora]);

        $this->db->table('wa_mensajes')
            ->where('instancia_id', $instancia)
            ->where('estado_envio', 'enviando')
            ->where('updated_at <', $limite)
            ->update(['estado_envio' => 'pendiente', 'updated_at' => $ahora]);

        $rows = $this->db->query(
            "SELECT m.id, c.wa_chat_id, m.cuerpo, m.tipo, m.tiene_media
               FROM wa_mensajes m
               JOIN wa_chats c ON c.id = m.chat_id
              WHERE m.instancia_id = ? AND m.estado_envio = 'pendiente'
              ORDER BY m.id
              LIMIT " . (int) $max . '
              FOR UPDATE',
            [$instancia]
        )->getResult();

        if ($rows) {
            $this->db->table('wa_mensajes')
                ->whereIn('id', array_map(static fn ($r) => $r->id, $rows))
                ->set('estado_envio', 'enviando')
                ->set('intentos', 'intentos + 1', false)
                ->set('updated_at', $ahora)
                ->update();
        }

        $this->db->transComplete();

        // Archivo adjunto: Node lo descarga desde /api/whatsapp/media/{id}
        foreach ($rows as $r) {
            $r->media = null;
            if ((int) $r->tiene_media === 1) {
                $r->media = $this->db->table('wa_mensajes_media')
                    ->select('id, mimetype, nombre_archivo')
                    ->where('mensaje_id', $r->id)
                    ->orderBy('id')
                    ->get()->getRow();
            }
            unset($r->tiene_media);
        }

        return $rows;
    }

    /** Archivo de un mensaje saliente de la instancia (para que Node lo envíe). */
    public function mediaSaliente(string $instancia, int $mediaId): ?object
    {
        return $this->db->table('wa_mensajes_media mm')
            ->select('mm.*')
            ->join('wa_mensajes m', 'm.id = mm.mensaje_id')
            ->where('mm.id', $mediaId)
            ->where('m.instancia_id', $instancia)
            ->where('m.direccion', 'saliente')
            ->get()->getRow();
    }

    /**
     * Resultado del envío informado por Node.
     * Si el webhook ya registró el mismo mensaje (como 'celular'), se fusiona en la fila de la outbox.
     */
    public function resultado(string $instancia, int $id, bool $ok, ?string $waMessageId, ?string $error, ?string $waChatId = null): bool
    {
        $ahora = date('Y-m-d H:i:s');
        $fila = $this->db->table('wa_mensajes')
            ->where('id', $id)->where('instancia_id', $instancia)->where('direccion', 'saliente')
            ->get()->getRow();

        if (!$fila) {
            return false;
        }

        if (!$ok) {
            $this->db->table('wa_mensajes')->where('id', $id)->update([
                'estado_envio' => 'error',
                'error'        => $this->corto($error ?: 'Error desconocido', 255),
                'updated_at'   => $ahora,
            ]);

            return true;
        }

        $this->db->transStart();

        if (!$waMessageId) {
            // Enviado, pero la API local no obtuvo el id: llegará por webhook con outbox_id
            $this->db->table('wa_mensajes')->where('id', $id)->update([
                'estado_envio' => 'enviado',
                'error'        => null,
                'ack'          => max(1, (int) $fila->ack),
                'enviado_at'   => $fila->enviado_at ?? $ahora,
                'updated_at'   => $ahora,
            ]);
            if ($waChatId && $fila->wa_message_id === null) {
                $this->corregirChatId($instancia, (int) $fila->chat_id, $waChatId);
            }
            $this->db->transComplete();

            return true;
        }

        $duplicado = $this->db->table('wa_mensajes')
            ->where('instancia_id', $instancia)
            ->where('wa_message_id', $waMessageId)
            ->where('id !=', $id)
            ->get()->getRow();

        $datos = [
            'estado_envio'  => 'enviado',
            'error'         => null,
            'wa_message_id' => $waMessageId,
            'ack'           => max(1, (int) $fila->ack),
            'enviado_at'    => $fila->enviado_at ?? $ahora,
            'updated_at'    => $ahora,
        ];

        if ($duplicado) {
            $datos['ack'] = max($datos['ack'], (int) $duplicado->ack);
            $datos['enviado_at'] = $this->primero($fila->enviado_at, $duplicado->enviado_at) ?? $ahora;
            $datos['entregado_at'] = $this->primero($fila->entregado_at, $duplicado->entregado_at);
            $datos['visto_at'] = $this->primero($fila->visto_at, $duplicado->visto_at);
            $this->db->table('wa_mensajes')->where('id', $duplicado->id)->delete();
        }

        $this->db->table('wa_mensajes')->where('id', $id)->update($datos);

        if ($waChatId) {
            $this->corregirChatId($instancia, (int) $fila->chat_id, $waChatId);
        }

        $this->db->transComplete();

        return true;
    }

    /**
     * Asocia el id real de WhatsApp a un mensaje de la outbox. Si el webhook ya
     * lo había guardado como fila aparte, se fusiona. Devuelve el id de la fila.
     */
    private function asociarEnvio(string $instancia, int $outboxId, string $waMessageId, string $waChatId, int $ack): ?int
    {
        $fila = $this->db->table('wa_mensajes')
            ->where('id', $outboxId)->where('instancia_id', $instancia)
            ->where('direccion', 'saliente')->where('origen', 'chat_web')
            ->get()->getRow();

        if (!$fila || ($fila->wa_message_id !== null && $fila->wa_message_id !== $waMessageId)) {
            return null;
        }

        if ($fila->wa_message_id === null) {
            $this->db->table('wa_mensajes')
                ->where('instancia_id', $instancia)->where('wa_message_id', $waMessageId)->where('id !=', $outboxId)
                ->delete();

            $this->db->table('wa_mensajes')->where('id', $outboxId)->update([
                'wa_message_id' => $waMessageId,
                'estado_envio'  => 'enviado',
                'error'         => null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        $this->aplicarAck($instancia, $waMessageId, max(1, $ack));
        $this->corregirChatId($instancia, (int) $fila->chat_id, $waChatId);

        return $outboxId;
    }

    /**
     * WhatsApp puede usar para el chat un id distinto al que se usó al enviar
     * (p. ej. @lid en vez de @c.us). Se actualiza el id o, si el webhook ya creó
     * un chat con el id real, se fusionan en ese.
     */
    private function corregirChatId(string $instancia, int $chatId, string $waChatId): void
    {
        $chat = $this->db->table('wa_chats')->where('id', $chatId)->get()->getRow();
        if (!$chat || $chat->wa_chat_id === $waChatId) {
            return;
        }

        $destino = $this->db->table('wa_chats')
            ->where('instancia_id', $instancia)->where('wa_chat_id', $waChatId)
            ->get()->getRow();

        if (!$destino) {
            $this->db->table('wa_chats')->where('id', $chatId)->update(['wa_chat_id' => $waChatId]);

            return;
        }

        $cambios = [];
        foreach (['agente_id', 'asignado_at', 'cliente_id', 'numero', 'nombre'] as $campo) {
            if ($destino->$campo === null && $chat->$campo !== null) {
                $cambios[$campo] = $chat->$campo;
            }
        }
        if ($chat->ultimo_mensaje_at !== null && ($destino->ultimo_mensaje_at === null || $chat->ultimo_mensaje_at > $destino->ultimo_mensaje_at)) {
            $cambios['ultimo_mensaje'] = $chat->ultimo_mensaje;
            $cambios['ultimo_mensaje_at'] = $chat->ultimo_mensaje_at;
        }
        if ($cambios) {
            $this->db->table('wa_chats')->where('id', $destino->id)->update($cambios);
        }

        $this->db->table('wa_mensajes')->where('chat_id', $chatId)->update(['chat_id' => $destino->id]);
        $this->db->table('wa_chat_eventos')->where('chat_id', $chatId)->update(['chat_id' => $destino->id]);
        $this->db->query('UPDATE IGNORE wa_chat_lecturas SET chat_id = ? WHERE chat_id = ?', [$destino->id, $chatId]);
        $this->db->table('wa_chats')->where('id', $chatId)->delete();
    }

    // ------------------------------------------------------------------ helpers

    private function obtenerOCrearChat(string $instancia, array $m, string $ahora): object
    {
        $waChatId = $m['wa_chat_id'];
        $chat = $this->db->table('wa_chats')
            ->where('instancia_id', $instancia)->where('wa_chat_id', $waChatId)
            ->get()->getRow();

        $numero = $this->normalizarNumero($m['numero'] ?? null);
        $nombre = $this->corto($m['chat_nombre'] ?? null, 150);

        if ($chat) {
            $cambios = [];
            if ($nombre && $nombre !== $chat->nombre && empty($m['from_me'])) {
                $cambios['nombre'] = $nombre;
            }
            if ($numero && !$chat->numero) {
                $cambios['numero'] = $numero;
            }
            if ($numero && !$chat->cliente_id && !(int) $chat->es_grupo) {
                $clienteId = $this->buscarCliente($numero);
                if ($clienteId) {
                    $cambios['cliente_id'] = $clienteId;
                }
            }
            if ($cambios) {
                $this->db->table('wa_chats')->where('id', $chat->id)->update($cambios);
                foreach ($cambios as $k => $v) {
                    $chat->$k = $v;
                }
            }

            return $chat;
        }

        $esGrupo = !empty($m['es_grupo']) ? 1 : 0;

        // INSERT IGNORE: si otro webhook lo creó a la vez, no falla
        $this->db->query(
            'INSERT IGNORE INTO wa_chats (instancia_id, wa_chat_id, numero, nombre, es_grupo, cliente_id, estado_chat, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$instancia, $waChatId, $numero, $nombre, $esGrupo, $esGrupo ? null : $this->buscarCliente($numero), 'abierto', $ahora, $ahora]
        );

        return $this->db->table('wa_chats')
            ->where('instancia_id', $instancia)->where('wa_chat_id', $waChatId)
            ->get()->getRow();
    }

    public function normalizarNumero(?string $numero): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $numero);
        if ($digitos === '') {
            return null;
        }
        if (strlen($digitos) === 9) {
            $digitos = '51' . $digitos;
        }

        return substr($digitos, 0, 20);
    }

    /** Busca el cliente por teléfono (clientes.telefono o contactos de tipo Celular/WhatsApp). */
    public function buscarCliente(?string $numero): ?int
    {
        if (!$numero) {
            return null;
        }

        $variantes = [$numero];
        if (strlen($numero) === 11 && str_starts_with($numero, '51')) {
            $variantes[] = substr($numero, 2);
        }
        $marcas = implode(',', array_fill(0, count($variantes), '?'));
        $limpio = "REPLACE(REPLACE(REPLACE(REPLACE(%s, ' ', ''), '-', ''), '+', ''), '.', '')";

        $row = $this->db->query(
            'SELECT id FROM clientes WHERE ' . sprintf($limpio, 'telefono') . " IN ($marcas) ORDER BY estado = 'activo' DESC, id LIMIT 1",
            $variantes
        )->getRow();

        if (!$row) {
            $row = $this->db->query(
                'SELECT cliente_id AS id FROM contactos_cliente
                  WHERE tipo_contacto_id IN (3, 4) AND activo = 1 AND ' . sprintf($limpio, 'valor') . " IN ($marcas)
                  ORDER BY es_principal DESC, id LIMIT 1",
                $variantes
            )->getRow();
        }

        return $row ? (int) $row->id : null;
    }

    private function guardarMedia(string $instancia, int $mensajeId, array $media, string $ahora): void
    {
        $contenido = base64_decode((string) $media['data'], true);
        if ($contenido === false || $contenido === '') {
            return;
        }

        $this->guardarArchivo($instancia, $mensajeId, $contenido, $media['mimetype'] ?? null, $media['filename'] ?? null, $ahora);
    }

    /** Guarda el archivo de un mensaje en writable/uploads/whatsapp y registra su fila en wa_mensajes_media. */
    public function guardarArchivo(string $instancia, int $mensajeId, string $contenido, ?string $mimetype, ?string $nombre, string $ahora): void
    {
        $media = ['filename' => $nombre];
        $mime = $this->corto($mimetype ?: 'application/octet-stream', 100);
        $ext = $this->extension($mime, $media['filename'] ?? null);
        $relativa = preg_replace('/[^a-zA-Z0-9_-]/', '', $instancia) . '/' . date('Y-m') . '/' . $mensajeId . '.' . $ext;
        $destino = WRITEPATH . 'uploads/whatsapp/' . $relativa;

        if (!is_dir(dirname($destino))) {
            mkdir(dirname($destino), 0775, true);
        }
        file_put_contents($destino, $contenido);

        $this->db->table('wa_mensajes_media')->insert([
            'mensaje_id'     => $mensajeId,
            'mimetype'       => $mime,
            'nombre_archivo' => $this->corto($media['filename'] ?? null, 255),
            'ruta'           => $relativa,
            'tamano_bytes'   => strlen($contenido),
            'created_at'     => $ahora,
        ]);
    }

    private function extension(string $mime, ?string $nombre): string
    {
        $ext = $nombre ? strtolower(pathinfo($nombre, PATHINFO_EXTENSION)) : '';
        if ($ext === '') {
            $mimeBase = strtolower(trim(explode(';', $mime)[0]));
            $ext = \Config\Mimes::guessExtensionFromType($mimeBase) ?? '';
            if ($ext === '' && str_contains($mimeBase, '/')) {
                $ext = explode('/', $mimeBase)[1];
            }
        }
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);

        return $ext !== '' && strlen($ext) <= 10 ? $ext : 'bin';
    }

    private function vistaPrevia(array $m): string
    {
        $texto = trim((string) ($m['cuerpo'] ?? ''));
        if ($texto === '') {
            $texto = self::ETIQUETAS_MEDIA[$m['tipo'] ?? ''] ?? '[mensaje]';
        }

        return mb_substr($texto, 0, 255);
    }

    private function registrarEvento(int $chatId, string $tipo, string $ahora): void
    {
        $this->db->table('wa_chat_eventos')->insert([
            'chat_id'    => $chatId,
            'usuario_id' => null,
            'tipo'       => $tipo,
            'created_at' => $ahora,
        ]);
    }

    private function primero(?string $a, ?string $b): ?string
    {
        if ($a === null) {
            return $b;
        }
        if ($b === null) {
            return $a;
        }

        return min($a, $b);
    }

    private function corto(?string $valor, int $max): ?string
    {
        if ($valor === null) {
            return null;
        }

        return mb_substr($valor, 0, $max);
    }
}
