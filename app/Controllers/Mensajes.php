<?php

namespace App\Controllers;

use App\Models\MensajeModel;

class Mensajes extends BaseController
{
    public function crear()
    {
        $db = \Config\Database::connect();
        $data['contactos'] = $db->query("
            SELECT cc.id, cc.contacto_nombre, cc.valor as telefono,
                   c.razon_social, c.ruc
            FROM contactos_cliente cc
            JOIN clientes c ON c.id = cc.cliente_id
            WHERE cc.activo = 1 AND c.estado = 'activo'
            ORDER BY c.razon_social, cc.contacto_nombre
        ")->getResult();

        return view('mensajes/crear', $data);
    }

    public function guardar()
    {
        $db = \Config\Database::connect();
        $data = $this->request->getPost();

        $titulo = trim($data['titulo'] ?? '');
        $contenido = trim($data['contenido'] ?? '');
        $contactosIds = $data['contactos'] ?? [];

        if (!$titulo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El título es obligatorio.',
            ]);
        }

        if (empty($contactosIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Seleccione al menos un contacto.',
            ]);
        }

        $db->transBegin();

        $mensajeId = $db->table('mensajes')->insert([
            'titulo'       => $titulo,
            'contenido'    => $contenido ?: null,
            'creado_por'   => session('user_id'),
            'estado'       => 'activo',
        ], true);

        if (!$mensajeId) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al crear el mensaje.',
            ]);
        }

        $contactos = $db->table('contactos_cliente cc')
            ->select('cc.id, cc.contacto_nombre, cc.valor as telefono, c.razon_social, c.ruc')
            ->join('clientes c', 'c.id = cc.cliente_id')
            ->whereIn('cc.id', $contactosIds)
            ->get()
            ->getResult();

        $envios = [];
        foreach ($contactos as $c) {
            $envios[] = [
                'mensaje_id'      => $mensajeId,
                'contacto_id'     => $c->id,
                'mensaje'         => $contenido ?: null,
                'estado'          => 'pendiente',
                'intentos'        => 0,
                'numero_whatsapp' => $c->telefono ?: null,
                'nombre_contacto' => $c->contacto_nombre ?: null,
                'razon_social'    => $c->razon_social ?: null,
                'ruc'             => $c->ruc ?: null,
            ];
        }

        if (!empty($envios)) {
            $db->table('envios_mensajes')->insertBatch($envios);
        }

        $db->transCommit();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Mensaje creado y envíos registrados correctamente.',
        ]);
    }

    public function listado()
    {
        return view('mensajes/listado');
    }

    public function listar()
    {
        $db = \Config\Database::connect();
        $rows = $db->query("
            SELECT m.*, u.nombres, u.apellidos,
                   (SELECT COUNT(*) FROM envios_mensajes em WHERE em.mensaje_id = m.id) as total_envios
            FROM mensajes m
            LEFT JOIN usuarios u ON u.id = m.creado_por
            WHERE m.estado = 'activo'
            ORDER BY m.fecha_creacion DESC
        ")->getResult();

        $data = [];
        foreach ($rows as $r) {
            $creador = $r->nombres ? esc($r->nombres . ' ' . $r->apellidos) : '—';

            $acciones = '<button class="btn btn-sm btn-soft-info editar-mensaje" data-id="' . $r->id . '" title="Editar">'
                . '<i data-lucide="pencil" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-secondary ver-envios" data-id="' . $r->id . '" title="Ver envíos">'
                . '<i data-lucide="eye" style="width:14px;height:14px;"></i></button> '
                . '<button class="btn btn-sm btn-soft-danger eliminar-mensaje" data-id="' . $r->id . '" title="Eliminar">'
                . '<i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>';

            $data[] = [
                esc($r->titulo),
                date('d/m/Y H:i', strtotime($r->fecha_creacion)),
                $creador,
                (int)$r->total_envios,
                '<span class="badge badge-soft-success">Activo</span>',
                $acciones,
                $r->id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function obtener($id)
    {
        $db = \Config\Database::connect();
        $mensaje = $db->table('mensajes')->where('id', $id)->get()->getRow();
        if (!$mensaje) {
            return $this->response->setJSON(['error' => 'Mensaje no encontrado.']);
        }

        $envios = $db->query("
            SELECT em.*
            FROM envios_mensajes em
            WHERE em.mensaje_id = ?
            ORDER BY em.fecha_envio DESC
        ", [$id])->getResult();

        return $this->response->setJSON([
            'mensaje' => $mensaje,
            'envios'  => $envios,
        ]);
    }

    public function actualizar($id)
    {
        $db = \Config\Database::connect();
        $mensaje = $db->table('mensajes')->where('id', $id)->get()->getRow();
        if (!$mensaje) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Mensaje no encontrado.',
            ]);
        }

        $data = $this->request->getPost();
        $update = [];

        if (isset($data['titulo'])) {
            $update['titulo'] = trim($data['titulo']);
        }
        if (isset($data['contenido'])) {
            $update['contenido'] = trim($data['contenido']) ?: null;
        }

        if (empty($update)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay datos para actualizar.',
            ]);
        }

        $db->table('mensajes')->where('id', $id)->update($update);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Mensaje actualizado correctamente.',
        ]);
    }

    public function eliminar($id)
    {
        $db = \Config\Database::connect();
        $mensaje = $db->table('mensajes')->where('id', $id)->get()->getRow();
        if (!$mensaje) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Mensaje no encontrado.',
            ]);
        }

        $db->table('mensajes')->where('id', $id)->update([
            'estado' => 'inactivo',
            'user_delete' => session('user_id'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Mensaje desactivado correctamente.',
        ]);
    }
}
