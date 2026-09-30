<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class PortatilModel extends Model
{
    protected $table            = 'garantias_portatiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'nombre_analista', 'numero_traslado', 'placa_id_equipo', 'tipo_gestion',
        'energiza', 'da_video', 'realizo_test_lenovo', 'estado_actual_equipo',
        'diagnostico_laptop_intervenido', 'garantia', 'porque_solicita_garantia',
        'numero_ticket', 'estado_final_equipo', 'indique_pieza', 'indique_fru',
        'pieza_intervenida', 'origen_pieza', 'motivo_baja', 'serial_disco', 'foto_ruta',
        'created_at'
    ];

    protected $validationRules  = [
        'placa_id_equipo' => 'required|min_length[2]|max_length[100]',
    ];

    /**
     * Busca el registro más reciente de una laptop por su placa o serial.
     */
    public function buscarUltimoPorPlaca(string $placa): ?array
    {
        return $this->where('placa_id_equipo', trim($placa))
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Cuenta diagnósticos de portátiles en un rango de fechas.
     */
    public function contarPorRango(?string $desde = null, ?string $hasta = null, ?string $analista = null): int
    {
        $builder = $this->builder();

        if ($desde !== null && trim($desde) !== '') {
            $builder->where('created_at >=', $desde);
        }

        if ($hasta !== null && trim($hasta) !== '') {
            $builder->where('created_at <=', $hasta);
        }

        if ($analista !== null && trim($analista) !== '') {
            $builder->where('nombre_analista', trim($analista));
        }

        return (int) $builder->countAllResults();
    }

    /**
     * Filtra registros de garantías y diagnósticos de portátiles para bitácora o exportación.
     */
    public function filtrarBitacora(?string $busqueda = null, ?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        $builder = $this->builder();

        if ($busqueda !== null && trim($busqueda) !== '') {
            $termino = trim($busqueda);
            $builder->groupStart()
                    ->like('placa_id_equipo', $termino)
                    ->orLike('numero_ticket', $termino)
                    ->orLike('nombre_analista', $termino)
                    ->groupEnd();
        }

        if ($fechaDesde !== null && trim($fechaDesde) !== '') {
            $builder->where('created_at >=', trim($fechaDesde) . ' 00:00:00');
        }

        if ($fechaHasta !== null && trim($fechaHasta) !== '') {
            $builder->where('created_at <=', trim($fechaHasta) . ' 23:59:59');
        }

        return $builder->orderBy('id', 'DESC')->get()->getResultArray();
    }

    /**
     * Cuenta el total general de registros de portátiles.
     */
    public function contarTotal(): int
    {
        return (int) $this->builder()->countAllResults();
    }
}