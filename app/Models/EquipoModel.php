<?php

namespace App\Models;

use CodeIgniter\Model;

class EquipoModel extends Model
{
    protected $table            = 'equipos';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'nombre_analista', 
        'num_traslado', 
        'placa_id', 
        'tipo_gestion',
        'energiza', 
        'da_video', 
        'estado_actual', 
        'que_va_intervenir',
        'origen_pieza', 
        'serial_disco', 
        'descripcion_novedad', 
        'motivo_baja',
        'ubicacion_destino', 
        'foto_equipo', 
        'fecha_creacion'
    ];
    protected $useTimestamps    = false;
}