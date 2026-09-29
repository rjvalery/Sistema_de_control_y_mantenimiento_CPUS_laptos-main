<?php

namespace App\Models;

use CodeIgniter\Model;

class SopladoModel extends Model
{
    protected $table            = 'soplado_registros';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'nombre_analista', 'num_traslado', 'placa_id', 'energiza', 
        'da_video', 'detecta_disco', 'ingreso_bios', 'pasta_termica', 
        'maquina_contenia', 'gel_cucarachas', 'foto_ruta',
        'created_at', 'fecha_creacion'
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
            if (!$this->db->fieldExists('created_at', $this->table) && !$this->db->fieldExists('fecha_creacion', $this->table)) {
                $this->db->query("ALTER TABLE `{$this->table}` ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP;");
            }
            self::$tablaAsegurada = true;
        } catch (\Throwable $e) {
            log_message('error', 'Error al asegurar columnas en ' . $this->table . ': ' . $e->getMessage());
        }
    }
}