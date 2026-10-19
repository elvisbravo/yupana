<?php

namespace App\Controllers;

use App\Models\WaSyncModel;

/**
 * API para la aplicación WhatsApp local (Node). Protegida con el filtro 'apitoken'.
 */
class ApiWhatsapp extends BaseController
{
    private WaSyncModel $model;

    public function __construct()
    {
        $this->model = new WaSyncModel();
    }

    /** Eventos que Node envía apenas ocurren: {tipo: 'mensaje'|'ack', instancia, ...}. */
    public function webhook()
    {
        $data = $this->request->getJSON(true);
        if (!is_array($data)) {
            return $this->error('JSON inválido.', 400);
        }

        $instancia = (string) ($data['instancia'] ?? '');
        if (!$this->model->instanciaExiste($instancia)) {
            return $this->error("Instancia '{$instancia}' no registrada.", 404);
        }

        switch ($data['tipo'] ?? '') {
            case 'mensaje':
                $m = $data['mensaje'] ?? null;
                if (!is_array($m) || empty($m['wa_message_id']) || empty($m['wa_chat_id'])) {
                    return $this->error('Faltan wa_message_id o wa_chat_id.', 422);
                }
                $id = $this->model->guardarMensaje($instancia, $m);

                return $this->response->setJSON(['success' => true, 'id' => $id]);

            case 'ack':
                if (empty($data['wa_message_id']) || !isset($data['ack'])) {
                    return $this->error('Faltan wa_message_id o ack.', 422);
                }
                $this->model->aplicarAck($instancia, (string) $data['wa_message_id'], (int) $data['ack']);

                return $this->response->setJSON(['success' => true]);

            default:
                return $this->error('Tipo de evento no soportado.', 422);
        }
    }

    /**
     * Node consulta cada pocos segundos: registra el latido y, si WhatsApp
     * está listo, entrega los mensajes pendientes de envío.
     */
    public function salientes()
    {
        $instancia = (string) $this->request->getGet('instancia');
        if (!$this->model->instanciaExiste($instancia)) {
            return $this->error("Instancia '{$instancia}' no registrada.", 404);
        }

        $this->model->latido(
            $instancia,
            (string) ($this->request->getGet('estado') ?: 'desconocido'),
            $this->request->getGet('numero') ?: null
        );

        $salientes = $this->request->getGet('listo') === '1'
            ? $this->model->tomarSalientes($instancia)
            : [];

        return $this->response->setJSON(['success' => true, 'salientes' => $salientes]);
    }

    /** Resultado del envío de un mensaje de la outbox: {instancia, ok, wa_message_id?, error?}. */
    public function resultado($id)
    {
        $data = $this->request->getJSON(true) ?? [];
        $instancia = (string) ($data['instancia'] ?? '');

        $ok = $this->model->resultado(
            $instancia,
            (int) $id,
            !empty($data['ok']),
            isset($data['wa_message_id']) ? (string) $data['wa_message_id'] : null,
            isset($data['error']) ? (string) $data['error'] : null,
            !empty($data['wa_chat_id']) ? (string) $data['wa_chat_id'] : null
        );

        return $ok
            ? $this->response->setJSON(['success' => true])
            : $this->error('Mensaje no encontrado.', 404);
    }

    /** Descarga del archivo de un mensaje saliente: GET media/{id}?instancia= */
    public function media($id)
    {
        $media = $this->model->mediaSaliente((string) $this->request->getGet('instancia'), (int) $id);

        $base = realpath(WRITEPATH . 'uploads/whatsapp');
        $ruta = $media && $base ? realpath($base . DIRECTORY_SEPARATOR . $media->ruta) : false;
        if (!$ruta || !str_starts_with($ruta, $base . DIRECTORY_SEPARATOR) || !is_file($ruta)) {
            return $this->error('Archivo no encontrado.', 404);
        }

        return $this->response
            ->setHeader('Content-Type', $media->mimetype)
            ->setBody(file_get_contents($ruta));
    }

    private function error(string $mensaje, int $status)
    {
        return $this->response->setStatusCode($status)->setJSON(['success' => false, 'message' => $mensaje]);
    }
}
