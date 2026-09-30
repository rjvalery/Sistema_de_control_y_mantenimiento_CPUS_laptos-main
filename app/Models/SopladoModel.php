<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class SopladoModel extends Model
{
    protected $table            = 'soplado_registros';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'nombre_analista', 'num_traslado', 'placa_id', 'energiza', 
        'da_video', 'detecta_disco', 'ingreso_bios', 'pasta_termica', 
        'maquina_contenia', 'gel_cucarachas', 'foto_ruta',
        'created_at', 'fecha_creacion'
    ];

    protected $validationRules  = [
        'placa_id' => 'required|min_length[2]|max_length[100]',
    ];

    /**
     * Cuenta registros de soplado en un rango de fechas.
     */
    public function contarPorRango(?string $desde = null, ?string $hasta = null): int
    {
        $builder = $this->builder();

        if ($desde !== null && trim($desde) !== '') {
            $builder->where('fecha_creacion >=', $desde);
        }

        if ($hasta !== null && trim($hasta) !== '') {
            $builder->where('fecha_creacion <=', $hasta);
        }

        return (int) $builder->countAllResults();
    }

    /**
     * Filtra registros de soplado para bitácora o exportación.
     */
    public function filtrarBitacora(?string $busqueda = null, ?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        $builder = $this->builder();

        if ($busqueda !== null && trim($busqueda) !== '') {
            $termino = trim($busqueda);
            $builder->groupStart()
                    ->like('placa_id', $termino)
                    ->orLike('num_traslado', $termino)
                    ->orLike('nombre_analista', $termino)
                    ->groupEnd();
        }

        if ($fechaDesde !== null && trim($fechaDesde) !== '') {
            $builder->where('fecha_creacion >=', trim($fechaDesde) . ' 00:00:00');
        }

        if ($fechaHasta !== null && trim($fechaHasta) !== '') {
            $builder->where('fecha_creacion <=', trim($fechaHasta) . ' 23:59:59');
        }

        return $builder->orderBy('id', 'DESC')->get()->getResultArray();
    }

    /**
     * Cuenta el total general de registros de soplado.
     */
    public function contarTotal(): int
    {
        return (int) $this->builder()->countAllResults();
    }
}