<?php

namespace App\Controllers;

use App\Models\WaChatModel;
use App\Models\WaSyncModel;

class ChatWhatsapp extends BaseController
{
    private const MAX_ARCHIVOS = 10;

    private const MAX_BYTES_ARCHIVO = 16 * 1024 * 1024;

    private const EXTENSIONES_BLOQUEADAS = [
        'exe', 'bat', 'cmd', 'com', 'scr', 'msi', 'js', 'vbs', 'jar', 'ps1', 'sh', 'php', 'phtml', 'phar',
    ];

    private WaChatModel $model;

    public function __construct()
    {
        $this->model = new WaChatModel();
    }

    public function index()
    {
        return view('chat_whatsapp/index', [
            'usuarioId'    => $this->usuarioId(),
            'esSupervisor' => $this->esSupervisor(),
            'agentes'      => $this->model->agentes(),
        ]);
    }

    public function chats()
    {
        $filtro = (string) $this->request->getGet('filtro');
        if (!in_array($filtro, ['todos', 'mios', 'sin_asignar'], true)) {
            $filtro = 'todos';
        }

        return $this->response->setJSON([
            'success'    => true,
            'chats'      => $this->model->listarChats(
                $this->usuarioId(),
                $this->esSupervisor(),
                $filtro,
                trim((string) $this->request->getGet('q'))
            ),
            'instancias' => $this->model->instancias(),
        ]);
    }

    public function mensajes($id)
    {
        $chat = $this->chatVisible((int) $id);
        if (!$chat) {
            return $this->noEncontrado();
        }

        $desdeId = (int) $this->request->getGet('desde_id');
        $mensajes = $this->model->mensajes((int) $chat->id, $desdeId);

        if ($mensajes || $desdeId === 0) {
            $this->model->marcarLeido((int) $chat->id, $this->usuarioId());
        }

        return $this->response->setJSON([
            'success'  => true,
            'chat'     => $this->chatPublico($chat),
            'mensajes' => $mensajes,
            'estados'  => $this->model->estadosSalientes((int) $chat->id),
        ]);
    }

    public function enviar($id)
    {
        $chat = $this->chatVisible((int) $id);
        if (!$chat) {
            return $this->noEncontrado();
        }

        $texto = trim((string) $this->request->getPost('mensaje'));
        if (mb_strlen($texto) > 4096) {
            return $this->error('El mensaje es demasiado largo (máximo 4096 caracteres).', 422);
        }

        $archivos = $this->validarArchivos();
        if (is_string($archivos)) {
            return $this->error($archivos, 422);
        }
        if ($texto === '' && !$archivos) {
            return $this->error('Escribe un mensaje.', 422);
        }

        $usuarioId = $this->usuarioId();

        if ($chat->agente_id === null && !$this->model->tomar((int) $chat->id, $usuarioId)) {
            // Otro usuario lo tomó justo antes
            $chat = $this->model->obtenerChat((int) $chat->id);
        }

        if ($chat->agente_id !== null && (int) $chat->agente_id !== $usuarioId && !$this->esSupervisor()) {
            return $this->error('Este chat ya lo atiende ' . ($chat->agente_nombre ?: 'otro usuario') . '.', 403);
        }

        if ($chat->estado_chat === 'cerrado') {
            $this->model->cambiarEstado((int) $chat->id, 'abierto', $usuarioId);
        }

        if ($archivos) {
            $mensajeId = $this->encolarArchivos($chat, $usuarioId, $texto, $archivos);
            if ($mensajeId === null) {
                return $this->error('No se pudo guardar el archivo. Intenta nuevamente.', 500);
            }
        } else {
            $mensajeId = $this->model->encolarMensaje($chat, $usuarioId, $texto);
        }
        $this->model->marcarLeido((int) $chat->id, $usuarioId);

        return $this->response->setJSON([
            'success'    => true,
            'mensaje_id' => $mensajeId,
            'chat'       => $this->chatPublico($this->model->obtenerChat((int) $chat->id)),
        ]);
    }

    /** Inicia (o reutiliza) un chat con un número y encola el primer mensaje. */
    public function nuevo()
    {
        $sync = new WaSyncModel();
        $numero = $sync->normalizarNumero((string) $this->request->getPost('numero'));
        if ($numero === null || strlen($numero) < 10 || strlen($numero) > 15) {
            return $this->error('Ingresa un número válido: 9 dígitos, o el número con su código de país.', 422);
        }

        $texto = trim((string) $this->request->getPost('mensaje'));
        if ($texto === '') {
            return $this->error('Escribe el primer mensaje.', 422);
        }
        if (mb_strlen($texto) > 4096) {
            return $this->error('El mensaje es demasiado largo (máximo 4096 caracteres).', 422);
        }

        $usuarioId = $this->usuarioId();
        $chat = $this->model->buscarPorNumero($numero);

        if ($chat) {
            if ($chat->agente_id === null) {
                $this->model->tomar((int) $chat->id, $usuarioId);
                $chat = $this->model->obtenerChat((int) $chat->id);
            }
            if ($chat->agente_id !== null && (int) $chat->agente_id !== $usuarioId && !$this->esSupervisor()) {
                return $this->error('Ese número ya tiene un chat atendido por ' . ($chat->agente_nombre ?: 'otro usuario') . '.', 403);
            }
            if ($chat->estado_chat === 'cerrado') {
                $this->model->cambiarEstado((int) $chat->id, 'abierto', $usuarioId);
            }
        } else {
            $clienteId = $sync->buscarCliente($numero);
            $nombre = null;
            if ($clienteId) {
                $cliente = \Config\Database::connect()->table('clientes')->select('razon_social')->where('id', $clienteId)->get()->getRow();
                $nombre = $cliente->razon_social ?? null;
            }
            $chat = $this->model->obtenerChat($this->model->crearChat($numero, $clienteId, $nombre, $usuarioId));
        }

        $this->model->encolarMensaje($chat, $usuarioId, $texto);
        $this->model->marcarLeido((int) $chat->id, $usuarioId);

        return $this->response->setJSON(['success' => true, 'chat_id' => (int) $chat->id]);
    }

    public function buscarClientes()
    {
        $q = trim((string) $this->request->getGet('q'));

        return $this->response->setJSON([
            'success'  => true,
            'clientes' => mb_strlen($q) >= 2 ? $this->model->buscarClientes($q) : [],
        ]);
    }

    public function transferir($id)
    {
        if (!$this->esSupervisor()) {
            return $this->error('Solo un supervisor puede transferir chats.', 403);
        }

        $chat = $this->model->obtenerChat((int) $id);
        if (!$chat) {
            return $this->noEncontrado();
        }

        $aUsuarioId = (int) $this->request->getPost('usuario_id') ?: null;
        if ($aUsuarioId !== null && !in_array($aUsuarioId, array_map(static fn ($a) => (int) $a->id, $this->model->agentes()), true)) {
            return $this->error('El usuario seleccionado no es válido.', 422);
        }

        $this->model->transferir((int) $chat->id, $aUsuarioId, $this->usuarioId());

        return $this->response->setJSON([
            'success' => true,
            'chat'    => $this->chatPublico($this->model->obtenerChat((int) $chat->id)),
        ]);
    }

    public function cerrar($id)
    {
        $chat = $this->chatVisible((int) $id);
        if (!$chat) {
            return $this->noEncontrado();
        }

        $usuarioId = $this->usuarioId();
        if ($chat->agente_id !== null && (int) $chat->agente_id !== $usuarioId && !$this->esSupervisor()) {
            return $this->error('Solo el agente asignado o un supervisor puede cerrar este chat.', 403);
        }

        $nuevo = $chat->estado_chat === 'cerrado' ? 'abierto' : 'cerrado';
        $this->model->cambiarEstado((int) $chat->id, $nuevo, $usuarioId);

        return $this->response->setJSON([
            'success' => true,
            'chat'    => $this->chatPublico($this->model->obtenerChat((int) $chat->id)),
        ]);
    }

    public function lecturas($id)
    {
        $chat = $this->chatVisible((int) $id);
        if (!$chat) {
            return $this->noEncontrado();
        }

        return $this->response->setJSON([
            'success'  => true,
            'lecturas' => $this->model->lecturas((int) $chat->id),
        ]);
    }

    public function media($id)
    {
        $media = $this->model->obtenerMedia((int) $id);
        if (!$media || !$this->chatVisible((int) $media->chat_id)) {
            return $this->response->setStatusCode(404)->setBody('Archivo no encontrado');
        }

        $base = realpath(WRITEPATH . 'uploads/whatsapp');
        $ruta = $base ? realpath($base . DIRECTORY_SEPARATOR . $media->ruta) : false;
        if (!$ruta || !str_starts_with($ruta, $base . DIRECTORY_SEPARATOR) || !is_file($ruta)) {
            return $this->response->setStatusCode(404)->setBody('Archivo no encontrado');
        }

        $nombre = $media->nombre_archivo ?: basename($ruta);

        return $this->response
            ->setHeader('Content-Type', $media->mimetype)
            ->setHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '', $nombre) . '"')
            ->setBody(file_get_contents($ruta));
    }

    /**
     * Valida los adjuntos del envío. Devuelve [['archivo' => UploadedFile, 'tipo' => ...], ...]
     * o un string con el error.
     */
    private function validarArchivos(): array|string
    {
        $subidos = $this->request->getFileMultiple('archivos') ?? [];
        $subidos = array_values(array_filter($subidos, static fn ($f) => $f && $f->getError() !== UPLOAD_ERR_NO_FILE));

        if (!$subidos) {
            return [];
        }
        if (count($subidos) > self::MAX_ARCHIVOS) {
            return 'Puedes enviar hasta ' . self::MAX_ARCHIVOS . ' archivos a la vez.';
        }

        $comoDocumento = $this->request->getPost('como_documento') === '1';
        $resultado = [];

        foreach ($subidos as $f) {
            $nombre = $f->getClientName();
            if (!$f->isValid()) {
                return "No se pudo subir «{$nombre}»: " . $f->getErrorString();
            }
            if ($f->getSize() > self::MAX_BYTES_ARCHIVO) {
                return "«{$nombre}» supera el límite de " . (self::MAX_BYTES_ARCHIVO / 1024 / 1024) . ' MB.';
            }
            $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if (in_array($ext, self::EXTENSIONES_BLOQUEADAS, true)) {
                return "No se permite enviar archivos .{$ext}.";
            }

            $mime = $f->getMimeType();
            if ($comoDocumento) {
                $tipo = 'document';
            } elseif (str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml') {
                $tipo = 'image';
            } elseif (in_array($mime, ['video/mp4', 'video/3gpp'], true)) {
                $tipo = 'video';
            } elseif (str_starts_with($mime, 'audio/')) {
                $tipo = 'audio';
            } else {
                $tipo = 'document';
            }

            $resultado[] = ['archivo' => $f, 'tipo' => $tipo, 'mime' => $mime];
        }

        return $resultado;
    }

    /**
     * Encola un mensaje por archivo (el texto va como caption del primero).
     * Todo en una transacción, para que la API local no tome un mensaje sin su archivo.
     */
    private function encolarArchivos(object $chat, int $usuarioId, string $texto, array $archivos): ?int
    {
        $sync = new WaSyncModel();
        $db = \Config\Database::connect();
        $ultimoId = null;

        $db->transStart();
        foreach ($archivos as $i => $a) {
            $ultimoId = $this->model->encolarMensaje($chat, $usuarioId, $i === 0 ? $texto : '', $a['tipo'], true);
            $sync->guardarArchivo(
                $chat->instancia_id,
                $ultimoId,
                (string) file_get_contents($a['archivo']->getTempName()),
                $a['mime'],
                $a['archivo']->getClientName(),
                date('Y-m-d H:i:s')
            );
        }
        $db->transComplete();

        return $db->transStatus() ? $ultimoId : null;
    }

    private function usuarioId(): int
    {
        return (int) session('user_id');
    }

    private function esSupervisor(): bool
    {
        static $cache = null;

        return $cache ??= $this->model->esSupervisor((int) session('rol_id'));
    }

    private function chatVisible(int $chatId): ?object
    {
        $chat = $this->model->obtenerChat($chatId);

        return $chat && $this->model->puedeVer($chat, $this->usuarioId(), $this->esSupervisor()) ? $chat : null;
    }

    private function chatPublico(object $chat): array
    {
        return [
            'id'            => (int) $chat->id,
            'nombre'        => $chat->nombre,
            'numero'        => $chat->numero,
            'es_grupo'      => (int) $chat->es_grupo,
            'cliente_id'    => $chat->cliente_id,
            'cliente_nombre'=> $chat->cliente_nombre,
            'agente_id'     => $chat->agente_id !== null ? (int) $chat->agente_id : null,
            'agente_nombre' => $chat->agente_nombre ?: null,
            'estado_chat'   => $chat->estado_chat,
        ];
    }

    private function noEncontrado()
    {
        return $this->error('Chat no encontrado.', 404);
    }

    private function error(string $mensaje, int $status)
    {
        return $this->response->setStatusCode($status)->setJSON(['success' => false, 'message' => $mensaje]);
    }
}
