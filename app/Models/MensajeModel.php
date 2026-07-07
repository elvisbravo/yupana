<?php

namespace App\Models;

use CodeIgniter\Model;

class MensajeModel extends Model
{
    protected $table            = 'mensajes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'titulo', 'contenido', 'fecha_creacion',
        'creado_por', 'user_delete', 'path_file',
        'envio_file', 'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = false;

    protected $validationRules = [
        'titulo'   => 'required|max_length[255]',
        'estado'   => 'permit_empty|in_list[activo,inactivo]',
    ];

    protected $validationMessages = [
        'titulo' => [
            'required' => 'El título es obligatorio.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
