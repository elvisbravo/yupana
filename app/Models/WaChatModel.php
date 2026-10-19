<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Chats de WhatsApp (tablas wa_*).
 *
 * Reglas de agentes:
 *  - Un chat sin agente lo toma el primero que responde.
 *  - Un agente ve sus chats y los que no tienen agente.
 *  - Un supervisor (roles.nivel >= NIVEL_SUPERVISOR) ve todos y puede transferir.
 */
class WaChatModel extends Model
{
    public const NIVEL_SUPERVISOR = 4;

    private const ETIQUETAS_TIPO = [
        'image'    => '[imagen]',
        'video'    => '[video]',
        'audio'    => '[audio]',
        'document' => '[documento]',
    ];

    /** Id de la única instancia de la API WhatsApp local (fija por ahora). */
    public const INSTANCIA_ID = 'yupana';

    /** Segundos sin contacto de la API local para considerarla desconectada. */
    public const SEGUNDOS_DESCONECTADO = 30;

    protected $table            = 'wa_chats';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'instancia_id', 'wa_chat_id', 'numero', 'nombre', 'es_grupo', 'cliente_id',
        'no_leidos', 'ultimo_mensaje', 'ultimo_mensaje_at', 'archivado',
        'agente_id', 'asignado_at', 'estado_chat',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function esSupervisor(?int $rolId): bool
    {
        if (!$rolId) {
            return false;
        }

        $rol = $this->db->table('roles')->select('nivel')->where('id', $rolId)->get()->getRow();

        return $rol && (int) $rol->nivel >= self::NIVEL_SUPERVISOR;
    }

    public function puedeVer(object $chat, int $usuarioId, bool $supervisor): bool
    {
        return $supervisor || $chat->agente_id === null || (int) $chat->agente_id === $usuarioId;
    }

    /**
     * Chats visibles para el usuario.
     * $filtro: 'todos' (todo lo visible), 'mios', 'sin_asignar'.
     */
    /*
     * no_leidos (por usuario): entrantes posteriores a su última lectura. Los mensajes
     * recuperados del historial con fecha anterior a esa lectura no cuentan.
     */
    public function listarChats(int $usuarioId, bool $supervisor, string $filtro = 'todos', string $q = ''): array
    {
        $b = $this->db->table('wa_chats c')
            ->select("c.id, c.instancia_id, c.wa_chat_id, c.numero, c.nombre, c.es_grupo, c.cliente_id,
                      c.ultimo_mensaje, c.ultimo_mensaje_at, c.agente_id, c.estado_chat,
                      cl.razon_social AS cliente_nombre,
                      TRIM(CONCAT(COALESCE(u.nombres, ''), ' ', COALESCE(u.apellidos, ''))) AS agente_nombre,
                      (SELECT COUNT(*) FROM wa_mensajes m
                        WHERE m.chat_id = c.id AND m.direccion = 'entrante'
                          AND m.id > COALESCE(l.ultimo_mensaje_id, 0)
                          AND (l.leido_at IS NULL OR m.fecha_mensaje > l.leido_at - INTERVAL 1 MINUTE)) AS no_leidos", false)
            ->join('wa_chat_lecturas l', 'l.chat_id = c.id AND l.usuario_id = ' . (int) $usuarioId, 'left')
            ->join('usuarios u', 'u.id = c.agente_id', 'left')
            ->join('clientes cl', 'cl.id = c.cliente_id', 'left')
            ->where('c.archivado', 0);

        if (!$supervisor) {
            $b->groupStart()->where('c.agente_id', $usuarioId)->orWhere('c.agente_id IS NULL')->groupEnd();
        }

        if ($filtro === 'mios') {
            $b->where('c.agente_id', $usuarioId);
        } elseif ($filtro === 'sin_asignar') {
            $b->where('c.agente_id IS NULL');
        }

        if ($q !== '') {
            $b->groupStart()
                ->like('c.nombre', $q)
                ->orLike('c.numero', $q)
                ->orLike('cl.razon_social', $q)
                ->groupEnd();
        }

        return $b->orderBy('c.ultimo_mensaje_at', 'DESC')
            ->orderBy('c.id', 'DESC')
            ->limit(300)
            ->get()->getResult();
    }

    public function obtenerChat(int $chatId): ?object
    {
        return $this->db->table('wa_chats c')
            ->select("c.*, cl.razon_social AS cliente_nombre,
                      TRIM(CONCAT(COALESCE(u.nombres, ''), ' ', COALESCE(u.apellidos, ''))) AS agente_nombre", false)
            ->join('usuarios u', 'u.id = c.agente_id', 'left')
            ->join('clientes cl', 'cl.id = c.cliente_id', 'left')
            ->where('c.id', $chatId)
            ->get()->getRow();
    }

    /**
     * Mensajes del chat. Sin $desdeId devuelve los últimos $limite;
     * con $desdeId solo los posteriores (para el polling).
     */
    public function mensajes(int $chatId, int $desdeId = 0, int $limite = 200): array
    {
        $b = $this->db->table('wa_mensajes m')
            ->select("m.id, m.direccion, m.origen, m.tipo, m.cuerpo, m.tiene_media, m.autor,
                      m.estado_envio, m.error, m.ack, m.fecha_mensaje, m.usuario_id,
                      TRIM(CONCAT(COALESCE(u.nombres, ''), ' ', COALESCE(u.apellidos, ''))) AS usuario_nombre", false)
            ->join('usuarios u', 'u.id = m.usuario_id', 'left')
            ->where('m.chat_id', $chatId);

        if ($desdeId > 0) {
            $rows = $b->where('m.id >', $desdeId)->orderBy('m.id', 'ASC')->limit($limite)->get()->getResult();
        } else {
            $rows = array_reverse($b->orderBy('m.id', 'DESC')->limit($limite)->get()->getResult());
        }

        return $this->adjuntarMedia($rows);
    }

    /** Estado actual de los últimos salientes, para refrescar los ticks. */
    public function estadosSalientes(int $chatId, int $limite = 50): array
    {
        return $this->db->table('wa_mensajes')
            ->select('id, origen, estado_envio, error, ack')
            ->where('chat_id', $chatId)
            ->where('direccion', 'saliente')
            ->orderBy('id', 'DESC')
            ->limit($limite)
            ->get()->getResult();
    }

    private function adjuntarMedia(array $rows): array
    {
        $ids = [];
        foreach ($rows as $r) {
            $r->media = [];
            if ((int) $r->tiene_media === 1) {
                $ids[] = $r->id;
            }
        }

        if ($ids) {
            $media = $this->db->table('wa_mensajes_media')
                ->select('id, mensaje_id, mimetype, nombre_archivo, tamano_bytes')
                ->whereIn('mensaje_id', $ids)
                ->get()->getResult();

            $porMensaje = [];
            foreach ($media as $m) {
                $porMensaje[$m->mensaje_id][] = $m;
            }
            foreach ($rows as $r) {
                $r->media = $porMensaje[$r->id] ?? [];
            }
        }

        return $rows;
    }

    /** Registra hasta qué mensaje leyó el usuario (nunca retrocede). */
    public function marcarLeido(int $chatId, int $usuarioId): void
    {
        $ultimo = $this->db->table('wa_mensajes')->selectMax('id')->where('chat_id', $chatId)->get()->getRow();
        $ultimoId = (int) ($ultimo->id ?? 0);
        if ($ultimoId === 0) {
            return;
        }

        $this->db->query(
            'INSERT INTO wa_chat_lecturas (chat_id, usuario_id, ultimo_mensaje_id, leido_at)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               leido_at = IF(VALUES(ultimo_mensaje_id) > ultimo_mensaje_id, VALUES(leido_at), leido_at),
               ultimo_mensaje_id = GREATEST(ultimo_mensaje_id, VALUES(ultimo_mensaje_id))',
            [$chatId, $usuarioId, $ultimoId, date('Y-m-d H:i:s')]
        );

        $this->db->table('wa_chats')->where('id', $chatId)->where('no_leidos >', 0)->update(['no_leidos' => 0]);
    }

    public function lecturas(int $chatId): array
    {
        return $this->db->table('wa_chat_lecturas l')
            ->select("l.usuario_id, l.ultimo_mensaje_id, l.leido_at,
                      TRIM(CONCAT(COALESCE(u.nombres, ''), ' ', COALESCE(u.apellidos, ''))) AS usuario_nombre", false)
            ->join('usuarios u', 'u.id = l.usuario_id', 'left')
            ->where('l.chat_id', $chatId)
            ->orderBy('l.leido_at', 'DESC')
            ->get()->getResult();
    }

    /**
     * Toma el chat si no tiene agente. Es atómico: si dos usuarios lo intentan
     * a la vez, solo uno lo consigue. Devuelve true si el usuario quedó como agente.
     */
    public function tomar(int $chatId, int $usuarioId): bool
    {
        $this->db->table('wa_chats')
            ->where('id', $chatId)
            ->where('agente_id IS NULL')
            ->update(['agente_id' => $usuarioId, 'asignado_at' => date('Y-m-d H:i:s')]);

        if ($this->db->affectedRows() === 1) {
            $this->registrarEvento($chatId, $usuarioId, 'asignado', null, $usuarioId);
            return true;
        }

        return false;
    }

    /** Transfiere a otro agente, o libera el chat si $aUsuarioId es null. */
    public function transferir(int $chatId, ?int $aUsuarioId, int $porUsuarioId): void
    {
        $chat = $this->find($chatId);

        $this->db->table('wa_chats')->where('id', $chatId)->update([
            'agente_id'   => $aUsuarioId,
            'asignado_at' => $aUsuarioId ? date('Y-m-d H:i:s') : null,
        ]);

        $this->registrarEvento(
            $chatId,
            $porUsuarioId,
            $aUsuarioId ? 'transferido' : 'liberado',
            $chat->agente_id !== null ? (int) $chat->agente_id : null,
            $aUsuarioId
        );
    }

    public function cambiarEstado(int $chatId, string $estado, int $porUsuarioId): void
    {
        $this->db->table('wa_chats')->where('id', $chatId)->update(['estado_chat' => $estado]);
        $this->registrarEvento($chatId, $porUsuarioId, $estado === 'cerrado' ? 'cerrado' : 'reabierto');
    }

    /** Chat existente de la instancia para ese número (por id de WhatsApp o por número). */
    public function buscarPorNumero(string $numero): ?object
    {
        $row = $this->db->table('wa_chats')
            ->select('id')
            ->where('instancia_id', self::INSTANCIA_ID)
            ->where('es_grupo', 0)
            ->groupStart()->where('wa_chat_id', $numero . '@c.us')->orWhere('numero', $numero)->groupEnd()
            ->orderBy('id')
            ->get()->getRow();

        return $row ? $this->obtenerChat((int) $row->id) : null;
    }

    /** Crea un chat nuevo iniciado desde Yupana, asignado a quien lo crea. */
    public function crearChat(string $numero, ?int $clienteId, ?string $nombre, int $usuarioId): int
    {
        $ahora = date('Y-m-d H:i:s');

        $this->db->table('wa_chats')->insert([
            'instancia_id' => self::INSTANCIA_ID,
            'wa_chat_id'   => $numero . '@c.us',
            'numero'       => $numero,
            'nombre'       => $nombre !== null ? mb_substr($nombre, 0, 150) : null,
            'es_grupo'     => 0,
            'cliente_id'   => $clienteId,
            'agente_id'    => $usuarioId,
            'asignado_at'  => $ahora,
            'estado_chat'  => 'abierto',
            'created_at'   => $ahora,
            'updated_at'   => $ahora,
        ]);
        $chatId = (int) $this->db->insertID();

        $this->registrarEvento($chatId, $usuarioId, 'asignado', null, $usuarioId, 'Chat iniciado desde Yupana');

        return $chatId;
    }

    public function buscarClientes(string $q): array
    {
        return $this->db->table('clientes')
            ->select('id, ruc, razon_social, telefono')
            ->where("TRIM(COALESCE(telefono, '')) !=", '')
            ->groupStart()->like('razon_social', $q)->orLike('ruc', $q)->orLike('nombre_comercial', $q)->groupEnd()
            ->orderBy("estado = 'activo'", 'DESC', false)
            ->orderBy('razon_social')
            ->limit(10)
            ->get()->getResult();
    }

    /** Encola un mensaje saliente para que la API local lo envíe. */
    public function encolarMensaje(object $chat, int $usuarioId, string $texto, string $tipo = 'chat', bool $tieneMedia = false): int
    {
        $ahora = date('Y-m-d H:i:s');

        $this->db->table('wa_mensajes')->insert([
            'chat_id'       => $chat->id,
            'instancia_id'  => $chat->instancia_id,
            'direccion'     => 'saliente',
            'origen'        => 'chat_web',
            'usuario_id'    => $usuarioId,
            'tipo'          => $tipo,
            'cuerpo'        => $texto !== '' ? $texto : null,
            'tiene_media'   => $tieneMedia ? 1 : 0,
            'estado_envio'  => 'pendiente',
            'fecha_mensaje' => $ahora,
            'created_at'    => $ahora,
            'updated_at'    => $ahora,
        ]);
        $id = (int) $this->db->insertID();

        $this->db->table('wa_chats')->where('id', $chat->id)->update([
            'ultimo_mensaje'    => mb_substr($texto !== '' ? $texto : (self::ETIQUETAS_TIPO[$tipo] ?? '[archivo]'), 0, 255),
            'ultimo_mensaje_at' => $ahora,
        ]);

        return $id;
    }

    public function registrarEvento(int $chatId, ?int $usuarioId, string $tipo, ?int $de = null, ?int $a = null, ?string $nota = null): void
    {
        $this->db->table('wa_chat_eventos')->insert([
            'chat_id'       => $chatId,
            'usuario_id'    => $usuarioId,
            'tipo'          => $tipo,
            'de_usuario_id' => $de,
            'a_usuario_id'  => $a,
            'nota'          => $nota,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function obtenerMedia(int $mediaId): ?object
    {
        return $this->db->table('wa_mensajes_media mm')
            ->select('mm.*, m.chat_id')
            ->join('wa_mensajes m', 'm.id = mm.mensaje_id')
            ->where('mm.id', $mediaId)
            ->get()->getRow();
    }

    /** Estado de las instancias de la API local (conectada = contacto reciente). */
    public function instancias(): array
    {
        $rows = $this->db->table('wa_instancias')
            ->select('id, label, numero, estado, ultimo_contacto_at')
            ->where('activo', 1)
            ->get()->getResult();

        $limite = time() - self::SEGUNDOS_DESCONECTADO;
        foreach ($rows as $r) {
            $r->latido_ok = $r->ultimo_contacto_at !== null && strtotime($r->ultimo_contacto_at) >= $limite;
            $r->conectada = $r->latido_ok && $r->estado === 'ready';
        }

        return $rows;
    }

    public function agentes(): array
    {
        return $this->db->table('usuarios')
            ->select("id, TRIM(CONCAT(nombres, ' ', COALESCE(apellidos, ''))) AS nombre", false)
            ->where('estado', 'activo')
            ->orderBy('nombres')
            ->get()->getResult();
    }
}
