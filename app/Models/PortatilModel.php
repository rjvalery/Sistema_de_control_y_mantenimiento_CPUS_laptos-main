<?php

namespace App\Models;

use CodeIgniter\Model;

class PortatilModel extends Model
{
    protected $table            = 'garantias_portatiles';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'nombre_analista', 'numero_traslado', 'placa_id_equipo', 'tipo_gestion',
        'energiza', 'da_video', 'realizo_test_lenovo', 'estado_actual_equipo',
        'diagnostico_laptop_intervenido', 'garantia', 'porque_solicita_garantia',
        'numero_ticket', 'estado_final_equipo', 'indique_pieza', 'indique_fru',
        'pieza_intervenida', 'origen_pieza', 'motivo_baja', 'serial_disco', 'foto_ruta'
    ];
    protected $useTimestamps    = false;

    private static bool $tablaAsegurada = false;

    public function __construct()
    {
        parent::__construct();
        $this->asegurarColumnas();
    }

    public function asegurarColumnas(): void
    {
        if (self::$tablaAsegurada) {
            return;
        }

        try {
            if (!$this->db->fieldExists('foto_ruta', $this->table)) {
                if ($this->db->fieldExists('foto_equipo', $this->table)) {
                    $this->db->query("ALTER TABLE `{$this->table}` CHANGE COLUMN `foto_equipo` `foto_ruta` TEXT NULL;");
                } else {
                    $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `foto_ruta` TEXT NULL;");
                }
            }

            if (!$this->db->fieldExists('indique_pieza', $this->table)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `indique_pieza` VARCHAR(150) NULL;");
            }
            if (!$this->db->fieldExists('indique_fru', $this->table)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `indique_fru` VARCHAR(150) NULL;");
            }
            if (!$this->db->fieldExists('motivo_baja', $this->table)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `motivo_baja` VARCHAR(255) NULL;");
            }
            if (!$this->db->fieldExists('serial_disco', $this->table)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `serial_disco` VARCHAR(120) NULL;");
            }

            self::$tablaAsegurada = true;
        } catch (\Throwable $e) {
            log_message('error', 'Error al asegurar columnas en ' . $this->table . ': ' . $e->getMessage());
        }
    }
}