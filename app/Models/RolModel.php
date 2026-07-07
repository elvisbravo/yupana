<?php

namespace App\Models;

use CodeIgniter\Model;

class RolModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'codigo', 'nombre', 'descripcion', 'nivel', 'activo',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'codigo' => 'required|max_length[20]|is_unique[roles.codigo,id,{id}]',
        'nombre' => 'required|max_length[80]',
        'nivel'  => 'permit_empty|is_natural_no_zero',
        'activo' => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'codigo' => [
            'required'  => 'El código es obligatorio.',
            'is_unique' => 'Este código ya está en uso.',
        ],
        'nombre' => [
            'required' => 'El nombre es obligatorio.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
