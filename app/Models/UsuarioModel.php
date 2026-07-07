<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'rol_id', 'nombres', 'apellidos', 'email',
        'password_hash', 'dni', 'telefono', 'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'rol_id'  => 'required|is_natural_no_zero',
        'nombres' => 'required|max_length[120]',
        'apellidos' => 'required|max_length[120]',
        'email'   => 'required|valid_email|is_unique[usuarios.email,id,{id}]',
        'dni'     => 'permit_empty|exact_length[8]|is_unique[usuarios.dni,id,{id}]',
        'estado'  => 'required|in_list[activo,inactivo,bloqueado]',
    ];

    protected $validationMessages = [
        'rol_id'  => ['required' => 'El rol es obligatorio.'],
        'nombres' => ['required' => 'El nombre es obligatorio.'],
        'apellidos' => ['required' => 'Los apellidos son obligatorios.'],
        'email'   => [
            'required'    => 'El email es obligatorio.',
            'valid_email' => 'Ingrese un email válido.',
            'is_unique'   => 'Este email ya está registrado.',
        ],
        'dni' => [
            'exact_length' => 'El DNI debe tener 8 dígitos.',
            'is_unique'    => 'Este DNI ya está registrado.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
