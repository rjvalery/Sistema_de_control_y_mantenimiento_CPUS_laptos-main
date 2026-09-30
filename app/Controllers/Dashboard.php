<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EquipoModel;
use App\Models\InventarioGeneralModel;
use App\Models\PortatilModel;
use App\Models\SopladoModel;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use Psr\Log\LoggerInterface;

class Dashboard extends BaseController
{
    protected UsuarioModel $usuarioModel;
    protected EquipoModel $equipoModel;
    protected SopladoModel $sopladoModel;
    protected PortatilModel $portatilModel;
    protected InventarioGeneralModel $inventarioModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->usuarioModel    = model(UsuarioModel::class);
        $this->equipoModel     = model(EquipoModel::class);
        $this->sopladoModel    = model(SopladoModel::class);
        $this->portatilModel   = model(PortatilModel::class);
        $this->inventarioModel = model(InventarioGeneralModel::class);
    }

    public function index(): string|ResponseInterface
    {
        $periodo = strtolower(trim((string)($this->request->getGet('periodo') ?? 'todos')));
        $rawAnalistaId = $this->request->getGet('analista_id');
        $analistaId = ($rawAnalistaId !== null && $rawAnalistaId !== '' && $rawAnalistaId !== 'todos') ? (int) $rawAnalistaId : null;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $data = $this->obtenerDatosMetricas($periodo, $analistaId, $page);
        $data['analistas'] = $this->usuarioModel->where('rol', 'analista')->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();

        if ($this->request->isAJAX() || $this->request->getGet('ajax') === '1') {
            return $this->response->setJSON($this->formatearRespuestaJson($data));
        }

        if (session('usuario_rol') === 'analista') {
            return view('dashboard/analista', $data);
        }

        return view('dashboard/index', $data);
    }

    /**
     * Endpoint API JSON para actualizar métricas en tiempo real vía AJAX según periodo, analista y página.
     */
    public function metricas(): ResponseInterface
    {
        $periodo = strtolower(trim((string)($this->request->getGet('periodo') ?? 'todos')));
        $rawAnalistaId = $this->request->getGet('analista_id');
        $analistaId = ($rawAnalistaId !== null && $rawAnalistaId !== '' && $rawAnalistaId !== 'todos') ? (int) $rawAnalistaId : null;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $data = $this->obtenerDatosMetricas($periodo, $analistaId, $page);

        return $this->response->setJSON($this->formatearRespuestaJson($data));
    }

    /**
     * Centraliza la estructura estándar del payload de respuesta JSON para AJAX y endpoints de métricas.
     */
    private function formatearRespuestaJson(array $data): array
    {
        return [
            'status'               => 'success',
            'periodo'              => $data['periodo'],
            'labelPeriodo'         => $data['labelPeriodo'],
            'analista_id'          => $data['analista_id'],
            'nombreAnalista'       => $data['nombreAnalista'],
            'statsInventario'      => $data['statsInventario'],
            'totalIntervenciones'  => $data['totalIntervenciones'],
            'totalEquipos'         => $data['totalEquipos'],
            'totalSoplado'         => $data['totalSoplado'],
            'totalPortatiles'      => $data['totalPortatiles'],
            'porcEq'               => $data['porcEq'],
            'porcSp'               => $data['porcSp'],
            'porcPt'               => $data['porcPt'],
            'totalTraslados'       => $data['totalTraslados'],
            'maquinasIntervenidas' => $data['maquinasIntervenidas'],
            'total_registros'      => $data['total_registros'],
            'total_paginas'        => $data['total_paginas'],
            'pagina_actual'        => $data['pagina_actual'],
            'limite'               => $data['limite'],
        ];
    }

    /**
     * Calcula los datos consolidados y métricas operativas según el periodo, analista y página seleccionados.
     */
    private function obtenerDatosMetricas(string $periodo, ?int $analistaId = null, int $page = 1): array
    {
        [$periodo, $fechaDesde, $fechaHasta, $labelPeriodo] = $this->resolverRangoFechas($periodo);

        // Resolver nombre del analista si se especifica analista_id
        $nombreAnalista = null;
        if ($analistaId !== null && $analistaId > 0) {
            $analistaRow = $this->usuarioModel->find($analistaId);
            if ($analistaRow) {
                $nombreAnalista = (string) ($analistaRow['nombre'] ?? '');
            }
        }

        // 1. Diagnóstico CPU en el periodo y analista
        $totalEquipos = $this->equipoModel->contarPorRango($fechaDesde, $fechaHasta, $nombreAnalista);

        // 2. Mantenimiento Soplado en el periodo y analista
        $totalSoplado = $this->sopladoModel->contarPorRango($fechaDesde, $fechaHasta, $nombreAnalista);

        // 3. Garantías Portátiles en el periodo y analista
        $totalPortatiles = $this->portatilModel->contarPorRango($fechaDesde, $fechaHasta, $nombreAnalista);

        // 4. Analistas registrados
        $totalAnalistas = $this->usuarioModel->contarPorRol('analista');

        // 5. Total intervenciones consolidadas
        $totalIntervenciones = $totalEquipos + $totalSoplado + $totalPortatiles;

        // 6. Inventario general y tabla paginada de intervenciones
        $statsInventario = $this->inventarioModel->obtenerEstadisticasInventario($fechaDesde, $fechaHasta, $nombreAnalista);
        $paginacion      = $this->inventarioModel->obtenerMaquinasIntervenidasPaginadas(10, $page, $fechaDesde, $fechaHasta, $analistaId, $nombreAnalista);
        $totalTraslados  = $this->inventarioModel->contarTrasladosRegistrados();

        // Porcentajes de participación por módulo técnico
        $porcEq = $totalIntervenciones > 0 ? round(($totalEquipos / $totalIntervenciones) * 100, 1) : 0;
        $porcSp = $totalIntervenciones > 0 ? round(($totalSoplado / $totalIntervenciones) * 100, 1) : 0;
        $porcPt = $totalIntervenciones > 0 ? round(($totalPortatiles / $totalIntervenciones) * 100, 1) : 0;

        return [
            'periodo'              => $periodo,
            'labelPeriodo'         => $labelPeriodo,
            'fechaDesde'           => $fechaDesde,
            'fechaHasta'           => $fechaHasta,
            'analista_id'          => $analistaId,
            'nombreAnalista'       => $nombreAnalista,
            'statsInventario'      => $statsInventario,
            'totalIntervenciones'  => $totalIntervenciones,
            'totalEquipos'         => $totalEquipos,
            'totalSoplado'         => $totalSoplado,
            'totalPortatiles'      => $totalPortatiles,
            'porcEq'               => $porcEq,
            'porcSp'               => $porcSp,
            'porcPt'               => $porcPt,
            'totalAnalistas'       => $totalAnalistas,
            'totalTraslados'       => $totalTraslados,
            'maquinasIntervenidas' => $paginacion['data'],
            'total_registros'      => $paginacion['total_registros'],
            'total_paginas'        => $paginacion['total_paginas'],
            'pagina_actual'        => $paginacion['pagina_actual'],
            'limite'               => $paginacion['limite'],
        ];
    }

    /**
     * Resuelve los límites de fecha y etiqueta descriptiva según el filtro de periodo.
     *
     * @param string $periodo dia|semana|mes|anio|todos
     * @return array [periodo, fechaDesde, fechaHasta, labelPeriodo]
     */
    private function resolverRangoFechas(string $periodo): array
    {
        $fechaDesde = null;
        $fechaHasta = null;
        $labelPeriodo = 'Histórico Completo';

        switch ($periodo) {
            case 'dia':
            case 'hoy':
                $periodo = 'dia';
                $fechaDesde = date('Y-m-d 00:00:00');
                $fechaHasta = date('Y-m-d 23:59:59');
                $labelPeriodo = 'Hoy (' . date('d/m/Y') . ')';
                break;

            case 'semana':
                $fechaDesde = date('Y-m-d 00:00:00', strtotime('monday this week'));
                $fechaHasta = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                $labelPeriodo = 'Esta Semana (' . date('d/m', strtotime('monday this week')) . ' al ' . date('d/m/Y', strtotime('sunday this week')) . ')';
                break;

            case 'mes':
                $fechaDesde = date('Y-m-01 00:00:00');
                $fechaHasta = date('Y-m-t 23:59:59');
                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                $mesNum = (int)date('n') - 1;
                $labelPeriodo = 'Este Mes (' . $meses[$mesNum] . ' ' . date('Y') . ')';
                break;

            case 'anio':
            case 'ano':
                $periodo = 'anio';
                $fechaDesde = date('Y-01-01 00:00:00');
                $fechaHasta = date('Y-12-31 23:59:59');
                $labelPeriodo = 'Este Año (' . date('Y') . ')';
                break;

            case 'todos':
            default:
                $periodo = 'todos';
                $fechaDesde = null;
                $fechaHasta = null;
                $labelPeriodo = 'Histórico Completo';
                break;
        }

        return [$periodo, $fechaDesde, $fechaHasta, $labelPeriodo];
    }
}

