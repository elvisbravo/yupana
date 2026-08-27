<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ruc', 'razon_social', 'nombre_comercial',
        'email', 'telefono', 'fecha_constitucion',
        'direccion', 'ubigeo_id', 'referencia',
        'regimen_actual_id', 'tipo_renta', 'presenta_balance', 'fecha_alta', 'fecha_baja',
        'estado', 'motivo_baja', 'observaciones',
        'usuario_registro_id', 'usuario_id_eliminado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id'                => 'permit_empty',
        'ruc'               => 'permit_empty|exact_length[11]|is_unique[clientes.ruc,id,{id}]',
        'email'             => 'permit_empty|valid_email|max_length[180]',
        'fecha_alta'        => 'required|valid_date',
        'regimen_actual_id' => 'permit_empty|is_natural_no_zero',
        'ubigeo_id'         => 'permit_empty|is_natural_no_zero',
    ];

    protected $validationMessages = [
        'ruc' => [
            'exact_length' => 'El RUC debe tener 11 dígitos.',
            'is_unique'    => 'Este RUC ya está registrado.',
        ],
        'fecha_alta' => [
            'required' => 'La fecha de alta es obligatoria.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['nullificarVacios'];
    protected $beforeUpdate = ['nullificarVacios'];

    /**
     * Los selects opcionales del formulario envían '' cuando no se elige nada.
     * MySQL convierte esa cadena a 0 en las columnas numéricas y rompe las
     * foreign keys, y en las de fecha la guarda como '0000-00-00'.
     */
    protected function nullificarVacios(array $data): array
    {
        $nullables = [
            'ruc', 'ubigeo_id', 'regimen_actual_id',
            'usuario_registro_id', 'usuario_id_eliminado',
            'fecha_constitucion', 'fecha_baja',
        ];

        foreach ($nullables as $campo) {
            if (($data['data'][$campo] ?? null) === '') {
                $data['data'][$campo] = null;
            }
        }

        return $data;
    }
}
