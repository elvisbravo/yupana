<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudContactoModel extends Model
{
    protected $table            = 'solicitudes_contacto';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombres', 'apellidos', 'email', 'telefono', 'servicio', 'mensaje', 'leido',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $validationRules = [
        'nombres'   => 'required|max_length[120]',
        'apellidos' => 'required|max_length[120]',
        'email'     => 'required|valid_email|max_length[180]',
        'telefono'  => 'permit_empty|numeric|exact_length[9]',
        'servicio'  => 'permit_empty|max_length[120]',
        'mensaje'   => 'required',
    ];

    protected $validationMessages = [
        'nombres' => [
            'required' => 'El nombre es obligatorio.',
        ],
        'apellidos' => [
            'required' => 'Los apellidos son obligatorios.',
        ],
        'email' => [
            'required'    => 'El correo electrónico es obligatorio.',
            'valid_email' => 'Ingrese un correo electrónico válido.',
        ],
        'telefono' => [
            'numeric'      => 'El teléfono debe contener solo números.',
            'exact_length' => 'El teléfono debe tener exactamente 9 dígitos.',
        ],
        'mensaje' => [
            'required' => 'El mensaje es obligatorio.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
