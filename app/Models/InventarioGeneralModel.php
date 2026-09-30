<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class InventarioGeneralModel extends Model
{
    protected $table         = 'inventario_general';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'identificador_1',
        'identificador_2',
        'num_traslado',
        'ref_principal',
        'descripcion',
        'zona_origen',
        'ubicacion_origen',
        'verificado',
        'observaciones',
        'placa_id',
        'serial',
        'tipo_equipo',
        'marca',
        'modelo',
        'ubicacion',
        'estado',
        'datos_adicionales',
        'archivo_origen',
        'usuario_cargue',
        'intervenido',
        'fecha_intervencion',
        'modulo_intervencion',
        'analista_intervencion',
        'created_at',
    ];
    protected $useTimestamps = false;
    protected $returnType    = 'array';

    private static bool $tablaAsegurada = false;

    /**
     * Crea la tabla en la base de datos si aún no existe y asegura las columnas requeridas.
     */
    public function asegurarTabla(): void
    {
        if (self::$tablaAsegurada) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `inventario_general` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `identificador_1` VARCHAR(100) NULL,
            `identificador_2` VARCHAR(100) NULL,
            `ref_principal` VARCHAR(150) NULL,
            `descripcion` VARCHAR(255) NULL,
            `zona_origen` VARCHAR(100) NULL,
            `ubicacion_origen` VARCHAR(150) NULL,
            `verificado` VARCHAR(50) NULL,
            `observaciones` TEXT NULL,
            `placa_id` VARCHAR(100) NULL,
            `serial` VARCHAR(100) NULL,
            `tipo_equipo` VARCHAR(80) NULL,
            `marca` VARCHAR(100) NULL,
            `modelo` VARCHAR(150) NULL,
            `ubicacion` VARCHAR(150) NULL,
            `estado` VARCHAR(80) NULL,
            `datos_adicionales` TEXT NULL,
            `archivo_origen` VARCHAR(255) NULL,
            `usuario_cargue` VARCHAR(120) NULL,
            `intervenido` TINYINT(1) DEFAULT 0,
            `fecha_intervencion` DATETIME NULL,
            `modulo_intervencion` VARCHAR(50) NULL,
            `analista_intervencion` VARCHAR(120) NULL,
            `created_at` DATETIME NULL,
            KEY `idx_id1` (`identificador_1`),
            KEY `idx_id2` (`identificador_2`),
            KEY `idx_placa` (`placa_id`),
            KEY `idx_serial` (`serial`),
            KEY `idx_intervenido` (`intervenido`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

        $this->db->query($sql);

        // Asegurar que las columnas del Formato en Cubic existan
        $columnasCubic = [
            'identificador_1'  => "ALTER TABLE `inventario_general` ADD COLUMN `identificador_1` VARCHAR(100) NULL AFTER `id`;",
            'identificador_2'  => "ALTER TABLE `inventario_general` ADD COLUMN `identificador_2` VARCHAR(100) NULL AFTER `identificador_1`;",
            'ref_principal'    => "ALTER TABLE `inventario_general` ADD COLUMN `ref_principal` VARCHAR(150) NULL AFTER `identificador_2`;",
            'descripcion'      => "ALTER TABLE `inventario_general` ADD COLUMN `descripcion` VARCHAR(255) NULL AFTER `ref_principal`;",
            'zona_origen'      => "ALTER TABLE `inventario_general` ADD COLUMN `zona_origen` VARCHAR(100) NULL AFTER `descripcion`;",
            'ubicacion_origen' => "ALTER TABLE `inventario_general` ADD COLUMN `ubicacion_origen` VARCHAR(150) NULL AFTER `zona_origen`;",
            'verificado'       => "ALTER TABLE `inventario_general` ADD COLUMN `verificado` VARCHAR(50) NULL AFTER `ubicacion_origen`;",
            'observaciones'    => "ALTER TABLE `inventario_general` ADD COLUMN `observaciones` TEXT NULL AFTER `verificado`;",
        ];

        foreach ($columnasCubic as $col => $alter) {
            if (!$this->db->fieldExists($col, 'inventario_general')) {
                $this->db->query($alter);
            }
        }

        // Si la tabla fue creada previamente sin las columnas de intervención o traslado, agregarlas
        if (!$this->db->fieldExists('num_traslado', 'inventario_general')) {
            $this->db->query("ALTER TABLE `inventario_general` ADD COLUMN `num_traslado` VARCHAR(100) NULL AFTER `identificador_2`;");
            $this->db->query("ALTER TABLE `inventario_general` ADD KEY `idx_num_traslado` (`num_traslado`);");
        }
        if (!$this->db->fieldExists('intervenido', 'inventario_general')) {
            $this->db->query("ALTER TABLE `inventario_general` ADD COLUMN `intervenido` TINYINT(1) DEFAULT 0 AFTER `usuario_cargue`;");
            $this->db->query("ALTER TABLE `inventario_general` ADD KEY `idx_intervenido` (`intervenido`);");
        }
        if (!$this->db->fieldExists('fecha_intervencion', 'inventario_general')) {
            $this->db->query("ALTER TABLE `inventario_general` ADD COLUMN `fecha_intervencion` DATETIME NULL AFTER `intervenido`;");
        }
        if (!$this->db->fieldExists('modulo_intervencion', 'inventario_general')) {
            $this->db->query("ALTER TABLE `inventario_general` ADD COLUMN `modulo_intervencion` VARCHAR(50) NULL AFTER `fecha_intervencion`;");
        }
        if (!$this->db->fieldExists('analista_intervencion', 'inventario_general')) {
            $this->db->query("ALTER TABLE `inventario_general` ADD COLUMN `analista_intervencion` VARCHAR(120) NULL AFTER `modulo_intervencion`;");
        }

        // Eliminar base de datos externa 'base_de_datos' si aún existe en MySQL
        try {
            $this->db->query("DROP DATABASE IF EXISTS `base_de_datos`;");
        } catch (\Throwable $e) {
            // Ignorar si no existen permisos o no existe la base de datos
        }

        // Eliminar filas fantasma de encabezados, totales o filas sin placa ni serial
        try {
            $this->db->query("DELETE FROM `inventario_general` 
                WHERE (
                    (identificador_1 IS NULL OR TRIM(identificador_1) IN ('', '-', '—', 'N/A', 'NA', 'SIN-PLACA'))
                    AND (identificador_2 IS NULL OR TRIM(identificador_2) IN ('', '-', '—', 'N/A', 'NA', 'SIN-SERIAL'))
                    AND (placa_id IS NULL OR TRIM(placa_id) IN ('', '-', '—', 'N/A', 'NA', 'SIN-PLACA'))
                    AND (serial IS NULL OR TRIM(serial) IN ('', '-', '—', 'N/A', 'NA', 'SIN-SERIAL'))
                )
                OR LOWER(TRIM(identificador_1)) IN ('identificador 1', 'identificador_1', 'identificador1', 'placa', 'placa_id', 'placa id', 'id 1', 'id1', 'activo', 'codigo', 'item', 'no', 'nro')
                OR LOWER(TRIM(identificador_2)) IN ('identificador 2', 'identificador_2', 'identificador2', 'serial', 'serie', 'id 2', 'id2', 'sn', 's/n')
                OR LOWER(TRIM(placa_id)) IN ('identificador 1', 'identificador_1', 'identificador1', 'placa', 'placa_id', 'placa id', 'total', 'totales')
                OR LOWER(TRIM(placa_id)) LIKE 'total%'
                OR LOWER(TRIM(identificador_1)) LIKE 'total%'
                OR LOWER(TRIM(identificador_2)) LIKE 'total%'
                OR LOWER(TRIM(descripcion)) LIKE 'total%';");
        } catch (\Throwable $e) {
            // Ignorar si la tabla aún se está construyendo
        }

        // Asegurar que registros antiguos tengan un created_at válido para filtros de tiempo
        try {
            $this->db->query("UPDATE `inventario_general` SET `created_at` = COALESCE(`fecha_intervencion`, NOW()) WHERE `created_at` IS NULL;");
        } catch (\Throwable $e) {}

        self::$tablaAsegurada = true;
    }

    /**
     * Busca un equipo en inventario por coincidencia exacta con sus identificadores únicos (placa o serial).
     * NUNCA busca por modelo/referencia para evitar mezclar equipos distintos.
     */
    public function buscarPorTermino(string $query): ?array
    {
        $this->asegurarTabla();
        $queryLimpia = trim($query);
        if ($queryLimpia === '') {
            return null;
        }
        $exacto = $this->builder()
            ->groupStart()
                ->where('identificador_1', $queryLimpia)
                ->orWhere('identificador_2', $queryLimpia)
                ->orWhere('placa_id', $queryLimpia)
                ->orWhere('serial', $queryLimpia)
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        return $exacto ?: null;
    }

    /**
     * Busca el número de traslado asociado a un equipo en inventario_general o en las tablas de bitácoras del sistema.
     * Respeta rigurosamente el traslado original de cargue masivo si ya existe.
     *
     * @param string $query Término buscado (placa o serial)
     * @param array|null $equipo Datos del equipo si ya fue encontrado en inventario_general
     * @return string|null El número de traslado si existe en el sistema
     */
    public function buscarTrasladoEnSistema(string $query, ?array $equipo = null): ?string
    {
        $this->asegurarTabla();
        $db = $this->db;

        // 1. Si el equipo ya viene de inventario_general y tiene num_traslado, retornar su traslado original
        if ($equipo && !empty($equipo['num_traslado'])) {
            return trim((string) $equipo['num_traslado']);
        }

        // 2. Extraer términos de búsqueda posibles estrictamente únicos (placa, serial, identificadores)
        $terminos = [$query];
        if ($equipo) {
            foreach (['placa_id', 'serial', 'identificador_1', 'identificador_2'] as $campo) {
                if (!empty($equipo[$campo])) {
                    $terminos[] = trim((string) $equipo[$campo]);
                }
            }
        }
        $terminos = array_values(array_unique(array_filter($terminos)));

        if (empty($terminos)) {
            return null;
        }

        // 3. Buscar en tabla `equipos` (Diagnóstico CPU)
        if ($db->tableExists('equipos')) {
            $reg = $db->table('equipos')
                ->select('num_traslado')
                ->whereIn('placa_id', $terminos)
                ->where('num_traslado IS NOT NULL')
                ->where('num_traslado !=', '')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if (!empty($reg['num_traslado'])) {
                return trim((string) $reg['num_traslado']);
            }
        }

        // 4. Buscar en tabla `soplado_registros`
        if ($db->tableExists('soplado_registros')) {
            $reg = $db->table('soplado_registros')
                ->select('num_traslado')
                ->whereIn('placa_id', $terminos)
                ->where('num_traslado IS NOT NULL')
                ->where('num_traslado !=', '')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if (!empty($reg['num_traslado'])) {
                return trim((string) $reg['num_traslado']);
            }
        }

        // 5. Buscar en tabla `garantias_portatiles`
        if ($db->tableExists('garantias_portatiles')) {
            $reg = $db->table('garantias_portatiles')
                ->select('numero_traslado')
                ->whereIn('placa_id_equipo', $terminos)
                ->where('numero_traslado IS NOT NULL')
                ->where('numero_traslado !=', '')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if (!empty($reg['numero_traslado'])) {
                return trim((string) $reg['numero_traslado']);
            }
        }

        return null;
    }

    /**
     * Busca si un equipo ya tiene historial en el sistema (equipos, soplado o portátiles)
     * aunque no haya estado en el cargue masivo.
     */
    public function buscarEnHistorialSistema(string $query): ?array
    {
        $db = $this->db;
        $termino = trim($query);
        if ($termino === '') {
            return null;
        }

        // 1. Diagnóstico CPU
        if ($db->tableExists('equipos')) {
            $reg = $db->table('equipos')
                ->where('placa_id', $termino)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if ($reg) {
                return [
                    'id'                    => 0,
                    'placa_id'              => $reg['placa_id'] ?? $termino,
                    'serial'                => $reg['serial_disco'] ?? '',
                    'num_traslado'          => $reg['num_traslado'] ?? null,
                    'tipo_equipo'           => 'CPU / Escritorio',
                    'marca'                 => '',
                    'modelo'                => '',
                    'ubicacion'             => $reg['ubicacion_destino'] ?? '',
                    'estado'                => $reg['estado_actual'] ?? '',
                    'intervenido'           => 1,
                    'fecha_intervencion'    => $reg['fecha_creacion'] ?? null,
                    'modulo_intervencion'   => 'Diagnóstico CPU',
                    'analista_intervencion' => $reg['nombre_analista'] ?? '',
                    'origen_datos'          => 'historial_sistema',
                ];
            }
        }

        // 2. Mantenimiento Soplado
        if ($db->tableExists('soplado_registros')) {
            $reg = $db->table('soplado_registros')
                ->where('placa_id', $termino)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if ($reg) {
                return [
                    'id'                    => 0,
                    'placa_id'              => $reg['placa_id'] ?? $termino,
                    'serial'                => '',
                    'num_traslado'          => $reg['num_traslado'] ?? null,
                    'tipo_equipo'           => 'CPU / Soplado',
                    'marca'                 => '',
                    'modelo'                => '',
                    'ubicacion'             => '',
                    'estado'                => 'Mantenimiento preventivo',
                    'intervenido'           => 1,
                    'fecha_intervencion'    => $reg['created_at'] ?? $reg['fecha_creacion'] ?? null,
                    'modulo_intervencion'   => 'Soplado',
                    'analista_intervencion' => $reg['nombre_analista'] ?? '',
                    'origen_datos'          => 'historial_sistema',
                ];
            }
        }

        // 3. Garantías Portátiles
        if ($db->tableExists('garantias_portatiles')) {
            $reg = $db->table('garantias_portatiles')
                ->where('placa_id_equipo', $termino)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            if ($reg) {
                return [
                    'id'                    => 0,
                    'placa_id'              => $reg['placa_id_equipo'] ?? $termino,
                    'serial'                => $reg['serial_disco'] ?? '',
                    'num_traslado'          => $reg['numero_traslado'] ?? null,
                    'tipo_equipo'           => 'Portátil',
                    'marca'                 => 'Lenovo',
                    'modelo'                => '',
                    'ubicacion'             => '',
                    'estado'                => $reg['estado_actual_equipo'] ?? '',
                    'intervenido'           => 1,
                    'fecha_intervencion'    => $reg['created_at'] ?? $reg['fecha_creacion'] ?? null,
                    'modulo_intervencion'   => 'Portátiles',
                    'analista_intervencion' => $reg['nombre_analista'] ?? '',
                    'origen_datos'          => 'historial_sistema',
                ];
            }
        }

        return null;
    }

    /**
     * Marca un equipo como intervenido en el inventario cuando se registra en cualquier formulario.
     */
    public function marcarIntervenido(string $placaOserial, string $modulo, string $analista): bool
    {
        $this->asegurarTabla();
        $termino = trim($placaOserial);
        if ($termino === '') {
            return false;
        }

        $equipo = $this->buscarPorTermino($termino);
        if (!$equipo) {
            return false;
        }

        // Se marca como intervenido y se conserva rigurosamente su num_traslado original de inventario
        return $this->update($equipo['id'], [
            'intervenido'           => 1,
            'fecha_intervencion'    => date('Y-m-d H:i:s'),
            'modulo_intervencion'   => $modulo,
            'analista_intervencion' => $analista,
        ]);
    }

    /**
     * Retorna las estadísticas consolidadas del inventario para el dashboard,
     * utilizando consultas agregadas directas (COUNT) en la base de datos sin cargar objetos.
     *
     * @param string|null $fechaDesde Fecha inicial en formato Y-m-d H:i:s
     * @param string|null $fechaHasta Fecha final en formato Y-m-d H:i:s
     * @return array Resumen estadístico
     */
    public function obtenerEstadisticasInventario(?string $fechaDesde = null, ?string $fechaHasta = null, ?string $nombreAnalista = null): array
    {
        $this->asegurarTabla();

        $filtroAnalista = ($nombreAnalista !== null && trim($nombreAnalista) !== '') ? trim($nombreAnalista) : null;

        if ($fechaDesde === null || $fechaHasta === null) {
            $builder = $this->builder();
            if ($filtroAnalista !== null) {
                $builder->where('analista_intervencion', $filtroAnalista);
            }

            $row = $builder
                ->select('COUNT(*) as total_cargados, SUM(CASE WHEN intervenido = 1 THEN 1 ELSE 0 END) as total_intervenidos')
                ->get()
                ->getRowArray();

            $totalCargados = (int) ($row['total_cargados'] ?? 0);
            $intervenidos  = (int) ($row['total_intervenidos'] ?? 0);
            $pendientes    = max(0, $totalCargados - $intervenidos);
            $porcentaje    = $totalCargados > 0 ? round(($intervenidos / $totalCargados) * 100, 1) : ($intervenidos > 0 ? 100.0 : 0.0);

            return [
                'totalCargados'   => $totalCargados,
                'cargadosPeriodo' => $totalCargados,
                'intervenidos'    => $intervenidos,
                'pendientes'      => $pendientes,
                'porcentaje'      => $porcentaje,
            ];
        }

        // Con filtro temporal mediante COUNT directo
        $builderCargados = $this->builder()
            ->select('COUNT(*) as cargados')
            ->where('created_at >=', $fechaDesde)
            ->where('created_at <=', $fechaHasta);

        if ($filtroAnalista !== null) {
            $builderCargados->where('analista_intervencion', $filtroAnalista);
        }

        $rowCargados = $builderCargados->get()->getRowArray();
        $cargadosPeriodo = (int) ($rowCargados['cargados'] ?? 0);

        $builderInterv = $this->builder()
            ->select('COUNT(*) as intervenidos')
            ->where('intervenido', 1)
            ->where('fecha_intervencion >=', $fechaDesde)
            ->where('fecha_intervencion <=', $fechaHasta);

        if ($filtroAnalista !== null) {
            $builderInterv->where('analista_intervencion', $filtroAnalista);
        }

        $rowInterv = $builderInterv->get()->getRowArray();
        $intervenidosPeriodo = (int) ($rowInterv['intervenidos'] ?? 0);

        $totalCargados = max($cargadosPeriodo, $intervenidosPeriodo);
        $pendientes    = max(0, $totalCargados - $intervenidosPeriodo);
        $porcentaje    = $totalCargados > 0 ? round(($intervenidosPeriodo / $totalCargados) * 100, 1) : ($intervenidosPeriodo > 0 ? 100.0 : 0.0);

        return [
            'totalCargados'   => $totalCargados,
            'cargadosPeriodo' => $cargadosPeriodo,
            'intervenidos'    => $intervenidosPeriodo,
            'pendientes'      => $pendientes,
            'porcentaje'      => $porcentaje,
        ];
    }

    /**
     * Realiza un mapeo y sincronización cruzando inventario_general con las tablas
     * equipos, soplado_registros y garantias_portatiles para localizar las máquinas
     * que ya se encuentran agregadas o intervenidas en el sistema.
     * NUNCA sobreescribe la columna num_traslado de inventario_general.
     *
     * @return array Resumen de sincronización con conteos y porcentajes
     */
    public function sincronizarConSistema(): array
    {
        $this->asegurarTabla();
        $db = $this->db;

        // Limpiar posibles filas artificiales previas
        try {
            $db->query("DELETE FROM `inventario_general` WHERE `observaciones` LIKE 'Mapeado desde%';");
        } catch (\Throwable $e) {}

        // Columnas canónicas de fecha de creación indexadas
        $fechaColEq = 'eq.fecha_creacion';
        $fechaColSp = 'sp.created_at';
        $fechaColGp = 'gp.created_at';

        // 1. Mapeo y cruce con tabla `equipos` (Diagnóstico CPUs / Escritorio)
        if ($db->tableExists('equipos')) {
            try {
                $sqlEquipos = "UPDATE `inventario_general` ig
                    INNER JOIN `equipos` eq ON (
                        (eq.placa_id IS NOT NULL AND eq.placa_id != '' AND (
                            ig.identificador_1 COLLATE utf8mb4_general_ci = eq.placa_id COLLATE utf8mb4_general_ci OR 
                            ig.identificador_2 COLLATE utf8mb4_general_ci = eq.placa_id COLLATE utf8mb4_general_ci OR 
                            ig.placa_id COLLATE utf8mb4_general_ci        = eq.placa_id COLLATE utf8mb4_general_ci OR 
                            ig.serial COLLATE utf8mb4_general_ci          = eq.placa_id COLLATE utf8mb4_general_ci
                        ))
                    )
                    SET 
                        ig.intervenido = 1,
                        ig.estado = 'Cargado',
                        ig.verificado = CASE 
                            WHEN ig.verificado IS NULL OR ig.verificado = '' OR ig.verificado = 'Pendiente' THEN 'Cargado' 
                            ELSE ig.verificado 
                        END,
                        ig.modulo_intervencion = COALESCE(ig.modulo_intervencion, 'Diagnóstico CPU'),
                        ig.analista_intervencion = COALESCE(ig.analista_intervencion, eq.nombre_analista),
                        ig.fecha_intervencion = COALESCE(ig.fecha_intervencion, {$fechaColEq}, NOW());";
                $db->query($sqlEquipos);
            } catch (\Throwable $e) {
                log_message('error', 'Error sincronizando con equipos: ' . $e->getMessage());
            }
        }

        // 2. Mapeo y cruce con tabla `soplado_registros` (Mantenimiento y Soplado)
        if ($db->tableExists('soplado_registros')) {
            try {
                $sqlSoplado = "UPDATE `inventario_general` ig
                    INNER JOIN `soplado_registros` sp ON (
                        (sp.placa_id IS NOT NULL AND sp.placa_id != '' AND (
                            ig.identificador_1 COLLATE utf8mb4_general_ci = sp.placa_id COLLATE utf8mb4_general_ci OR 
                            ig.identificador_2 COLLATE utf8mb4_general_ci = sp.placa_id COLLATE utf8mb4_general_ci OR 
                            ig.placa_id COLLATE utf8mb4_general_ci        = sp.placa_id COLLATE utf8mb4_general_ci OR 
                            ig.serial COLLATE utf8mb4_general_ci          = sp.placa_id COLLATE utf8mb4_general_ci
                        ))
                    )
                    SET 
                        ig.intervenido = 1,
                        ig.estado = 'Cargado',
                        ig.verificado = CASE 
                            WHEN ig.verificado IS NULL OR ig.verificado = '' OR ig.verificado = 'Pendiente' THEN 'Cargado' 
                            ELSE ig.verificado 
                        END,
                        ig.modulo_intervencion = COALESCE(ig.modulo_intervencion, 'Mantenimiento Soplado'),
                        ig.analista_intervencion = COALESCE(ig.analista_intervencion, sp.nombre_analista),
                        ig.fecha_intervencion = COALESCE(ig.fecha_intervencion, {$fechaColSp}, NOW());";
                $db->query($sqlSoplado);
            } catch (\Throwable $e) {
                log_message('error', 'Error sincronizando con soplado_registros: ' . $e->getMessage());
            }
        }

        // 3. Mapeo y cruce con tabla `garantias_portatiles` (Laptops / Portátiles)
        if ($db->tableExists('garantias_portatiles')) {
            try {
                $sqlPortatiles = "UPDATE `inventario_general` ig
                    INNER JOIN `garantias_portatiles` gp ON (
                        (gp.placa_id_equipo IS NOT NULL AND gp.placa_id_equipo != '' AND (
                            ig.identificador_1 COLLATE utf8mb4_general_ci = gp.placa_id_equipo COLLATE utf8mb4_general_ci OR 
                            ig.identificador_2 COLLATE utf8mb4_general_ci = gp.placa_id_equipo COLLATE utf8mb4_general_ci OR 
                            ig.placa_id COLLATE utf8mb4_general_ci        = gp.placa_id_equipo COLLATE utf8mb4_general_ci OR 
                            ig.serial COLLATE utf8mb4_general_ci          = gp.placa_id_equipo COLLATE utf8mb4_general_ci
                        ))
                    )
                    SET 
                        ig.intervenido = 1,
                        ig.estado = 'Cargado',
                        ig.verificado = CASE 
                            WHEN ig.verificado IS NULL OR ig.verificado = '' OR ig.verificado = 'Pendiente' THEN 'Cargado' 
                            ELSE ig.verificado 
                        END,
                        ig.modulo_intervencion = COALESCE(ig.modulo_intervencion, 'Garantía Portátiles'),
                        ig.analista_intervencion = COALESCE(ig.analista_intervencion, gp.nombre_analista),
                        ig.fecha_intervencion = COALESCE(ig.fecha_intervencion, {$fechaColGp}, NOW());";
                $db->query($sqlPortatiles);
            } catch (\Throwable $e) {
                log_message('error', 'Error sincronizando con garantias_portatiles: ' . $e->getMessage());
            }
        }

        $totalCargados = (int) $this->countAllResults();
        $totalIntervenidos = (int) $this->where('intervenido', 1)->countAllResults();
        $totalPendientes = max(0, $totalCargados - $totalIntervenidos);

        return [
            'totalCargados'     => $totalCargados,
            'totalIntervenidos' => $totalIntervenidos,
            'totalPendientes'   => $totalPendientes,
            'porcentaje'        => $totalCargados > 0 ? round(($totalIntervenidos / $totalCargados) * 100, 1) : 0,
        ];
    }

    /**
     * Mapea las máquinas que pertenecen a un traslado específico o a todos los traslados en inventario_general,
     * verificando cuáles ya se intervinieron en diagnósticos, soplado o portátiles,
     * y colocándoles el estatus de 'Cargado' e intervenido = 1.
     * NUNCA sobreescribe el número de traslado de ningún equipo.
     *
     * @param string|null $numTraslado Número de traslado opcional (ej: '318739' o '318196'), o null para todos
     * @return array Resumen del mapeo con detalle de conteos
     */
    public function mapearTraslado(?string $numTraslado = null): array
    {
        $this->asegurarTabla();
        $db = $this->db;

        // Limpiar cualquier fila artificial previa
        try {
            $db->query("DELETE FROM `inventario_general` WHERE `observaciones` LIKE 'Mapeado desde%';");
        } catch (\Throwable $e) {}

        // Columnas canónicas de fecha de creación indexadas
        $fechaColEq = 'eq.fecha_creacion';
        $fechaColSp = 'sp.created_at';
        $fechaColGp = 'gp.created_at';

        $whereTraslado = '';
        if (!empty($numTraslado)) {
            $escapedTraslado = $db->escape($numTraslado);
            $whereTraslado = " WHERE ig.num_traslado = {$escapedTraslado}";
        }

        // Cruce con Diagnóstico CPUs
        if ($db->tableExists('equipos')) {
            $db->query("UPDATE `inventario_general` ig
                INNER JOIN `equipos` eq ON (
                    (eq.placa_id IS NOT NULL AND eq.placa_id != '' AND (
                        ig.identificador_1 COLLATE utf8mb4_general_ci = eq.placa_id COLLATE utf8mb4_general_ci OR 
                        ig.identificador_2 COLLATE utf8mb4_general_ci = eq.placa_id COLLATE utf8mb4_general_ci OR 
                        ig.placa_id COLLATE utf8mb4_general_ci        = eq.placa_id COLLATE utf8mb4_general_ci OR 
                        ig.serial COLLATE utf8mb4_general_ci          = eq.placa_id COLLATE utf8mb4_general_ci
                    ))
                    OR
                    (eq.serial_disco IS NOT NULL AND eq.serial_disco != '' AND (
                        ig.identificador_1 COLLATE utf8mb4_general_ci = eq.serial_disco COLLATE utf8mb4_general_ci OR 
                        ig.identificador_2 COLLATE utf8mb4_general_ci = eq.serial_disco COLLATE utf8mb4_general_ci OR 
                        ig.placa_id COLLATE utf8mb4_general_ci        = eq.serial_disco COLLATE utf8mb4_general_ci OR 
                        ig.serial COLLATE utf8mb4_general_ci          = eq.serial_disco COLLATE utf8mb4_general_ci
                    ))
                )
                SET 
                    ig.intervenido = 1,
                    ig.estado = 'Cargado',
                    ig.verificado = 'Cargado',
                    ig.modulo_intervencion = COALESCE(ig.modulo_intervencion, 'Diagnóstico CPU'),
                    ig.analista_intervencion = COALESCE(ig.analista_intervencion, eq.nombre_analista),
                    ig.fecha_intervencion = COALESCE(ig.fecha_intervencion, {$fechaColEq}, NOW())
                {$whereTraslado};");
        }

        // Cruce con Mantenimiento y Soplado
        if ($db->tableExists('soplado_registros')) {
            $db->query("UPDATE `inventario_general` ig
                INNER JOIN `soplado_registros` sp ON (
                    sp.placa_id IS NOT NULL AND sp.placa_id != '' AND (
                        ig.identificador_1 COLLATE utf8mb4_general_ci = sp.placa_id COLLATE utf8mb4_general_ci OR 
                        ig.identificador_2 COLLATE utf8mb4_general_ci = sp.placa_id COLLATE utf8mb4_general_ci OR 
                        ig.placa_id COLLATE utf8mb4_general_ci        = sp.placa_id COLLATE utf8mb4_general_ci OR 
                        ig.serial COLLATE utf8mb4_general_ci          = sp.placa_id COLLATE utf8mb4_general_ci
                    )
                )
                SET 
                    ig.intervenido = 1,
                    ig.estado = 'Cargado',
                    ig.verificado = 'Cargado',
                    ig.modulo_intervencion = COALESCE(ig.modulo_intervencion, 'Mantenimiento Soplado'),
                    ig.analista_intervencion = COALESCE(ig.analista_intervencion, sp.nombre_analista),
                    ig.fecha_intervencion = COALESCE(ig.fecha_intervencion, {$fechaColSp}, NOW())
                {$whereTraslado};");
        }

        // Cruce con Garantías Portátiles
        if ($db->tableExists('garantias_portatiles')) {
            $db->query("UPDATE `inventario_general` ig
                INNER JOIN `garantias_portatiles` gp ON (
                    (gp.placa_id_equipo IS NOT NULL AND gp.placa_id_equipo != '' AND (
                        ig.identificador_1 COLLATE utf8mb4_general_ci = gp.placa_id_equipo COLLATE utf8mb4_general_ci OR 
                        ig.identificador_2 COLLATE utf8mb4_general_ci = gp.placa_id_equipo COLLATE utf8mb4_general_ci OR 
                        ig.placa_id COLLATE utf8mb4_general_ci        = gp.placa_id_equipo COLLATE utf8mb4_general_ci OR 
                        ig.serial COLLATE utf8mb4_general_ci          = gp.placa_id_equipo COLLATE utf8mb4_general_ci
                    ))
                    OR
                    (gp.serial_disco IS NOT NULL AND gp.serial_disco != '' AND (
                        ig.identificador_1 COLLATE utf8mb4_general_ci = gp.serial_disco COLLATE utf8mb4_general_ci OR 
                        ig.identificador_2 COLLATE utf8mb4_general_ci = gp.serial_disco COLLATE utf8mb4_general_ci OR 
                        ig.placa_id COLLATE utf8mb4_general_ci        = gp.serial_disco COLLATE utf8mb4_general_ci OR 
                        ig.serial COLLATE utf8mb4_general_ci          = gp.serial_disco COLLATE utf8mb4_general_ci
                    ))
                )
                SET 
                    ig.intervenido = 1,
                    ig.estado = 'Cargado',
                    ig.verificado = 'Cargado',
                    ig.modulo_intervencion = COALESCE(ig.modulo_intervencion, 'Garantía Portátiles'),
                    ig.analista_intervencion = COALESCE(ig.analista_intervencion, gp.nombre_analista),
                    ig.fecha_intervencion = COALESCE(ig.fecha_intervencion, {$fechaColGp}, NOW())
                {$whereTraslado};");
        }

        $bTotal = $this->builder();
        $bInterv = $this->builder()->where('intervenido', 1);
        if (!empty($numTraslado)) {
            $bTotal->where('num_traslado', $numTraslado);
            $bInterv->where('num_traslado', $numTraslado);
        }
        $totalTraslado = (int) $bTotal->countAllResults();
        $intervenidos = (int) $bInterv->countAllResults();

        return [
            'num_traslado'  => $numTraslado ?? 'todos',
            'totalTraslado' => $totalTraslado,
            'intervenidos'  => $intervenidos,
            'pendientes'    => max(0, $totalTraslado - $intervenidos),
        ];
    }

    /**
     * Mapea exclusivamente las máquinas del traslado 318196 (mantenido por retrocompatibilidad).
     *
     * @return array Resumen del mapeo con detalle de conteos
     */
    public function mapearTraslado318196(): array
    {
        return $this->mapearTraslado('318196');
    }

    /**
     * Obtiene la lista ordenada de números de traslado registrados en inventario_general.
     *
     * @return array<int, string>
     */
    public function obtenerTrasladosRegistrados(): array
    {
        $this->asegurarTabla();
        $filas = $this->builder()
            ->select('num_traslado')
            ->where('num_traslado IS NOT NULL')
            ->where('num_traslado !=', '')
            ->groupBy('num_traslado')
            ->orderBy('num_traslado', 'ASC')
            ->get()
            ->getResultArray();

        return array_column($filas, 'num_traslado');
    }

    /**
     * Cuenta directamente el total de traslados únicos registrados mediante una query agregada COUNT(DISTINCT).
     */
    public function contarTrasladosRegistrados(): int
    {
        $this->asegurarTabla();
        $row = $this->builder()
            ->select('COUNT(DISTINCT num_traslado) as total')
            ->where('num_traslado IS NOT NULL')
            ->where('num_traslado !=', '')
            ->get()
            ->getRowArray();

        return (int) ($row['total'] ?? 0);
    }

    /**
    /**
     * Obtiene las máquinas intervenidas paginadas con soporte de filtros por fecha y analista técnico.
     *
     * @param int $limite Cantidad de registros por página
     * @param int $pagina Número de página actual (1-indexed)
     * @param string|null $fechaDesde Fecha inicial en formato Y-m-d H:i:s
     * @param string|null $fechaHasta Fecha final en formato Y-m-d H:i:s
     * @param int|null $analistaId ID del analista en la tabla usuarios
     * @param string|null $nombreAnalista Nombre del analista (opcional)
     * @return array{data: array<int, array<string, mixed>>, total_registros: int, total_paginas: int, pagina_actual: int, limite: int}
     */
    public function obtenerMaquinasIntervenidasPaginadas(
        int $limite = 10,
        int $pagina = 1,
        ?string $fechaDesde = null,
        ?string $fechaHasta = null,
        ?int $analistaId = null,
        ?string $nombreAnalista = null
    ): array {
        $this->asegurarTabla();

        $limite = max(1, $limite);
        $pagina = max(1, $pagina);
        $offset = ($pagina - 1) * $limite;

        // Si se provee analistaId pero no nombreAnalista, obtener datos del analista desde usuarios
        $usuarioAnalista = null;
        if ($analistaId !== null && $analistaId > 0 && empty($nombreAnalista)) {
            if ($this->db->tableExists('usuarios')) {
                $u = $this->db->table('usuarios')->where('id', $analistaId)->get()->getRowArray();
                if ($u) {
                    $nombreAnalista = $u['nombre'] ?? null;
                    $usuarioAnalista = $u['usuario'] ?? null;
                }
            }
        }

        // 1. Conteo total con los mismos filtros
        $builderCount = $this->builder()->where('intervenido', 1);

        if ($fechaDesde !== null && $fechaHasta !== null) {
            $builderCount->where('fecha_intervencion >=', $fechaDesde)
                         ->where('fecha_intervencion <=', $fechaHasta);
        }

        if ($analistaId !== null && $analistaId > 0) {
            $builderCount->groupStart();
            if ($this->db->fieldExists('analista_id', 'inventario_general')) {
                $builderCount->where('analista_id', $analistaId);
            }
            if (!empty($nombreAnalista)) {
                $builderCount->orWhere('analista_intervencion', $nombreAnalista);
            }
            if (!empty($usuarioAnalista)) {
                $builderCount->orWhere('analista_intervencion', $usuarioAnalista);
            }
            $builderCount->groupEnd();
        }

        $totalRegistros = (int) $builderCount->countAllResults();

        // 2. Consulta de registros paginados
        $builder = $this->builder()
            ->select('id, identificador_1, identificador_2, placa_id, serial, num_traslado, ref_principal, modelo, descripcion, modulo_intervencion, analista_intervencion, fecha_intervencion')
            ->where('intervenido', 1);

        if ($fechaDesde !== null && $fechaHasta !== null) {
            $builder->where('fecha_intervencion >=', $fechaDesde)
                    ->where('fecha_intervencion <=', $fechaHasta);
        }

        if ($analistaId !== null && $analistaId > 0) {
            $builder->groupStart();
            if ($this->db->fieldExists('analista_id', 'inventario_general')) {
                $builder->where('analista_id', $analistaId);
            }
            if (!empty($nombreAnalista)) {
                $builder->orWhere('analista_intervencion', $nombreAnalista);
            }
            if (!empty($usuarioAnalista)) {
                $builder->orWhere('analista_intervencion', $usuarioAnalista);
            }
            $builder->groupEnd();
        }

        $intervenidas = $builder->orderBy('fecha_intervencion', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit($limite, $offset)
            ->get()
            ->getResultArray();

        // 3. Respaldo de visualización si no se ha sincronizado inventario_general
        if (empty($intervenidas) && $totalRegistros === 0 && $this->db->tableExists('equipos')) {
            $builderCountEq = $this->db->table('equipos');
            if ($fechaDesde !== null && $fechaHasta !== null) {
                $builderCountEq->where('fecha_creacion >=', $fechaDesde)
                               ->where('fecha_creacion <=', $fechaHasta);
            }
            if (!empty($nombreAnalista)) {
                $builderCountEq->where('nombre_analista', $nombreAnalista);
            }
            $totalRegistros = (int) $builderCountEq->countAllResults();

            $builderEq = $this->db->table('equipos')
                ->select('id, placa_id as identificador_1, serial_disco as identificador_2, num_traslado, nombre_analista as analista_intervencion, fecha_creacion as fecha_intervencion, "Diagnóstico CPU" as modulo_intervencion, tipo_gestion as descripcion, "Intervenido" as estado, 1 as intervenido')
                ->orderBy('id', 'DESC')
                ->limit($limite, $offset);

            if ($fechaDesde !== null && $fechaHasta !== null) {
                $builderEq->where('fecha_creacion >=', $fechaDesde)
                          ->where('fecha_creacion <=', $fechaHasta);
            }
            if (!empty($nombreAnalista)) {
                $builderEq->where('nombre_analista', $nombreAnalista);
            }

            $intervenidas = $builderEq->get()->getResultArray();
        }

        $totalPaginas = $totalRegistros > 0 ? (int) ceil($totalRegistros / $limite) : 1;

        return [
            'data'            => $intervenidas,
            'total_registros' => $totalRegistros,
            'total_paginas'   => $totalPaginas,
            'pagina_actual'   => $pagina,
            'limite'          => $limite,
        ];
    }

    /**
     * Retorna el listado de máquinas intervenidas recientes con soporte opcional de analista.
     *
     * @param int $limite Cantidad máxima de registros a retornar
     * @param string|null $fechaDesde Fecha inicial en formato Y-m-d H:i:s
     * @param string|null $fechaHasta Fecha final en formato Y-m-d H:i:s
     * @param int|null $analistaId ID opcional de analista
     * @param int $pagina Número de página (1-indexed)
     * @return array<int, array<string, mixed>>
     */
    public function obtenerMaquinasIntervenidasRecientes(
        int $limite = 8, 
        ?string $fechaDesde = null, 
        ?string $fechaHasta = null,
        ?int $analistaId = null,
        int $pagina = 1
    ): array {
        $resultado = $this->obtenerMaquinasIntervenidasPaginadas($limite, $pagina, $fechaDesde, $fechaHasta, $analistaId);
        return $resultado['data'];
    }

    /**
     * Filtra registros de inventario general según búsqueda, estado de intervención y traslado.
     */
    public function filtrarInventario(?string $busqueda = null, ?string $filtro = null, ?string $traslado = null, int $limite = 250): array
    {
        $builder = $this->builder();

        if ($filtro === 'agregados' || $filtro === 'intervenidos') {
            $builder->where('intervenido', 1);
        } elseif ($filtro === 'pendientes') {
            $builder->where('intervenido', 0);
        }

        if ($traslado !== null && trim($traslado) !== '') {
            $builder->where('num_traslado', trim($traslado));
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $termino = trim($busqueda);
            $builder->groupStart()
                ->like('identificador_1', $termino)
                ->orLike('identificador_2', $termino)
                ->orLike('num_traslado', $termino)
                ->orLike('ref_principal', $termino)
                ->orLike('descripcion', $termino)
                ->orLike('zona_origen', $termino)
                ->orLike('ubicacion_origen', $termino)
                ->orLike('verificado', $termino)
                ->orLike('observaciones', $termino)
                ->orLike('placa_id', $termino)
                ->orLike('serial', $termino)
                ->orLike('modulo_intervencion', $termino)
                ->orLike('analista_intervencion', $termino)
                ->groupEnd();
        }

        return $builder->orderBy('id', 'DESC')->limit($limite)->get()->getResultArray();
    }
}

