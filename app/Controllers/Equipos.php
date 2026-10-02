<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EquipoModel;
use App\Models\UsuarioModel;
use App\Models\InventarioGeneralModel;
use App\Services\UploadService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Equipos extends BaseController
{
    protected EquipoModel $equipoModel;
    protected UsuarioModel $usuarioModel;
    protected UploadService $uploadService;
    protected InventarioGeneralModel $inventarioModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->equipoModel     = model(EquipoModel::class);
        $this->usuarioModel    = model(UsuarioModel::class);
        $this->inventarioModel = model(InventarioGeneralModel::class);
        $this->uploadService   = new UploadService();
    }

    public function formulario(): string
    {
        $data['analistas'] = $this->usuarioModel->obtenerAnalistasActivos();
        return view('equipos/formulario', $data);
    }

    public function guardar(): ResponseInterface
    {
        $placaId     = (string) ($this->request->getPost('placa_id') ?? '');
        $tipoGestion = (string) ($this->request->getPost('tipo_gestion') ?? '');
        $foto         = $this->request->getFile('foto_equipo');
        $tieneArchivo = $foto && $foto->isValid() && !$foto->hasMoved();
        $esBaja       = ($tipoGestion === 'Baja');

        // Validación con servicio nativo de CodeIgniter 4
        $rules = [
            'foto_equipo' => [
                'label'  => 'Evidencia fotográfica',
                'rules'  => ($esBaja && !$tieneArchivo)
                    ? 'permit_empty'
                    : 'uploaded[foto_equipo]|is_image[foto_equipo]|mime_in[foto_equipo,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto_equipo,4096]',
                'errors' => [
                    'uploaded' => 'Debe adjuntar una evidencia fotográfica del equipo.',
                    'is_image' => 'El archivo seleccionado debe ser una imagen válida.',
                    'mime_in'  => 'El formato de imagen debe ser JPG, JPEG, PNG o WEBP.',
                    'max_size' => 'El tamaño de la imagen no puede superar los 4MB.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            $msg = $this->validator->getError('foto_equipo') ?: 'Error en la validación de la imagen.';
            return $this->respondError($msg);
        }

        // Subida y organización de evidencias mediante UploadService (Año/Mes/Día y nombrado por placa_id)
        $fotoRuta = null;
        if ($tieneArchivo) {
            $fotoRuta = $this->uploadService->guardarEvidencia($foto, $placaId, 'diagnostico');
            if (!$fotoRuta && !$esBaja) {
                return $this->respondError('No se pudo guardar la fotografía de evidencia.');
            }
        }

        $nombreAnalista = (session('usuario_rol') === 'analista')
            ? (string) session('usuario_nombre')
            : (string) ($this->request->getPost('nombre_analista') ?: session('usuario_nombre'));

        $serialDisco = trim((string) ($this->request->getPost('serial_disco') ?: $this->request->getPost('serial_disco_baja')));
        $descripcionNovedad = trim((string) ($this->request->getPost('descripcion_novedad') ?: $this->request->getPost('descripcion_it')));

        $data = [
            'nombre_analista'     => $nombreAnalista,
            'num_traslado'        => $this->request->getPost('num_traslado'),
            'placa_id'            => $placaId,
            'tipo_gestion'        => $this->request->getPost('tipo_gestion'),
            'energiza'            => $this->request->getPost('energiza'),
            'da_video'            => $this->request->getPost('da_video'),
            'estado_actual'       => $this->request->getPost('estado_actual'),
            'que_va_intervenir'   => $this->request->getPost('que_va_intervenir'),
            'origen_pieza'        => $this->request->getPost('origen_pieza'),
            'serial_disco'        => $serialDisco !== '' ? $serialDisco : null,
            'descripcion_novedad' => $descripcionNovedad !== '' ? $descripcionNovedad : null,
            'motivo_baja'         => $this->request->getPost('motivo_baja'),
            'ubicacion_destino'   => $this->request->getPost('ubicacion_destino'),
            'foto_equipo'         => $fotoRuta,
            'fecha_creacion'      => date('Y-m-d H:i:s')
        ];

        try {
            if ($this->equipoModel->insert($data)) {
                // Sincronizar y descontar de pendientes en inventario general
                $this->inventarioModel->marcarIntervenido((string)$placaId, 'Diagnóstico CPU', $nombreAnalista);

                return $this->respondSuccess([], 'Guardado correctamente');
            }

            $errores = $this->equipoModel->errors();
            $msgError = !empty($errores) ? implode(', ', $errores) : 'Verifique los datos ingresados.';
            return $this->respondError('Error de validación al guardar: ' . $msgError);
        } catch (\Throwable $e) {
            log_message('error', 'Error al guardar diagnóstico: ' . $e->getMessage());
            return $this->respondError('Error al guardar en base de datos: ' . $e->getMessage());
        }
    }

    public function bitacora(): string
    {
        $busqueda   = trim((string) ($this->request->getGet('buscar') ?? ''));
        $fechaDesde = trim((string) ($this->request->getGet('fecha_desde') ?? ''));
        $fechaHasta = trim((string) ($this->request->getGet('fecha_hasta') ?? ''));

        $registros = $this->equipoModel->filtrarBitacora(
            $busqueda !== '' ? $busqueda : null,
            $fechaDesde !== '' ? $fechaDesde : null,
            $fechaHasta !== '' ? $fechaHasta : null
        );

        $data = [
            'registros'      => $registros,
            'totalFiltrados' => count($registros),
            'totalGeneral'   => $this->equipoModel->contarTotal(),
            'busqueda'       => $busqueda,
            'fechaDesde'     => $fechaDesde,
            'fechaHasta'     => $fechaHasta,
        ];

        return view('equipos/bitacora', $data);
    }

    public function exportar(): ResponseInterface
    {
        $busqueda   = trim((string) ($this->request->getGet('buscar') ?? ''));
        $fechaDesde = trim((string) ($this->request->getGet('fecha_desde') ?? ''));
        $fechaHasta = trim((string) ($this->request->getGet('fecha_hasta') ?? ''));

        $registros = $this->equipoModel->filtrarBitacora(
            $busqueda !== '' ? $busqueda : null,
            $fechaDesde !== '' ? $fechaDesde : null,
            $fechaHasta !== '' ? $fechaHasta : null
        );

        $filename = "Reporte_Equipos_Diagnostico_" . date('Ymd_His') . ".csv";
        $headers = [
            'ID', 'Fecha/Hora', 'Analista', 'N° Traslado',
            'Placa ID', 'Gestión', 'Energiza', 'Da Video',
            'Estado Actual', 'Intervención', 'Origen Pieza', 'Novedad',
            'Motivo Baja', 'Serial Disco', 'Ubicación Destino'
        ];

        $data = [];
        foreach ($registros as $row) {
            $data[] = [
                $row['id'],
                $row['fecha_creacion'],
                $row['nombre_analista'] ?? '',
                $row['num_traslado'] ?? '',
                $row['placa_id'] ?? '',
                $row['tipo_gestion'] ?? '',
                $row['energiza'] ?? '-',
                $row['da_video'] ?? '-',
                $row['estado_actual'] ?? '-',
                $row['que_va_intervenir'] ?? '-',
                $row['origen_pieza'] ?? '-',
                $row['descripcion_novedad'] ?? '-',
                $row['motivo_baja'] ?? '-',
                $row['serial_disco'] ?? '-',
                $row['ubicacion_destino'] ?? '-'
            ];
        }

        return $this->exportarCsvResponse($headers, $data, $filename);
    }
}