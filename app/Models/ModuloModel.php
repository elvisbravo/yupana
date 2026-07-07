<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuloModel extends Model
{
    protected $table            = 'modulos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'padre_id', 'codigo', 'nombre', 'descripcion',
        'icono', 'ruta', 'orden', 'activo',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'codigo'  => 'required|max_length[80]|is_unique[modulos.codigo,id,{id}]',
        'nombre'  => 'required|max_length[120]',
        'icono'   => 'permit_empty|max_length[60]',
        'ruta'    => 'permit_empty|max_length[120]',
        'orden'   => 'permit_empty|is_natural',
        'activo'  => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'codigo' => [
            'required'   => 'El código es obligatorio.',
            'is_unique'  => 'Este código ya está en uso.',
            'max_length' => 'El código no debe exceder 80 caracteres.',
        ],
        'nombre' => [
            'required' => 'El nombre es obligatorio.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
