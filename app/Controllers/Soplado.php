<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SopladoModel;
use App\Models\UsuarioModel;
use App\Models\InventarioGeneralModel;
use App\Services\UploadService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Soplado extends BaseController
{
    protected SopladoModel $sopladoModel;
    protected UsuarioModel $usuarioModel;
    protected UploadService $uploadService;
    protected InventarioGeneralModel $inventarioModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->sopladoModel    = model(SopladoModel::class);
        $this->usuarioModel    = model(UsuarioModel::class);
        $this->inventarioModel = model(InventarioGeneralModel::class);
        $this->uploadService   = new UploadService();
    }

    public function formulario(): string
    {
        $data['analistas'] = $this->usuarioModel->obtenerAnalistasActivos();
        return view('soplado/formulario', $data);
    }

    public function guardar(): ResponseInterface
    {
        $placaId = (string) $this->request->getPost('placa_id');
        $file    = $this->request->getFile('foto_equipo');
        $isAjax  = $this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        $rules = [
            'placa_id'    => 'required|min_length[2]|max_length[100]',
            'foto_equipo' => [
                'label'  => 'Evidencia fotográfica',
                'rules'  => 'uploaded[foto_equipo]|is_image[foto_equipo]|mime_in[foto_equipo,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto_equipo,4096]',
                'errors' => [
                    'uploaded' => 'La foto de evidencia es obligatoria.',
                    'is_image' => 'El archivo seleccionado debe ser una imagen válida.',
                    'mime_in'  => 'El formato de imagen debe ser JPG, JPEG, PNG o WEBP.',
                    'max_size' => 'El tamaño de la imagen no puede superar los 4MB.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            $msg = current($this->validator->getErrors()) ?: 'Error en la validación.';
            return $isAjax ? $this->respondError($msg) : redirect()->back()->withInput()->with('error', $msg);
        }

        $fotoRuta = $this->uploadService->guardarEvidencia($file, $placaId, 'soplado');
        if (!$fotoRuta) {
            $msg = 'No se pudo guardar la fotografía de evidencia.';
            return $isAjax ? $this->respondError($msg) : redirect()->back()->withInput()->with('error', $msg);
        }

        $nombreAnalista = (session('usuario_rol') === 'analista')
            ? (string) session('usuario_nombre')
            : (string) ($this->request->getPost('nombre_analista') ?: session('usuario_nombre'));

        $maquinaContenia = (string) $this->request->getPost('maquina_contenia');
        $gelCucarachas   = ($maquinaContenia === 'Cucaracha')
            ? (string) ($this->request->getPost('gel_cucarachas') ?: 'No')
            : 'No';

        $data = [
            'nombre_analista'  => $nombreAnalista,
            'num_traslado'     => $this->request->getPost('num_traslado'),
            'placa_id'         => $placaId,
            'energiza'         => $this->request->getPost('energiza'),
            'da_video'         => $this->request->getPost('da_video'),
            'detecta_disco'    => $this->request->getPost('detecta_disco'),
            'ingreso_bios'     => $this->request->getPost('ingreso_bios'),
            'pasta_termica'    => $this->request->getPost('pasta_termica'),
            'maquina_contenia' => $maquinaContenia,
            'gel_cucarachas'   => $gelCucarachas,
            'foto_ruta'        => $fotoRuta,
            'fecha_creacion'   => date('Y-m-d H:i:s'),
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        if ($this->sopladoModel->insert($data)) {
            // Sincronizar y descontar de pendientes en inventario general
            $this->inventarioModel->marcarIntervenido((string)$placaId, 'soplado', $nombreAnalista);

            if ($isAjax) {
                return $this->respondSuccess([], 'Registro y evidencia guardados correctamente.');
            }
            return redirect()->to(base_url('soplado/formulario'))->with('msg', 'Registro y evidencia guardados correctamente.');
        }

        if ($isAjax) {
            return $this->respondError('Error al guardar en la base de datos.');
        }

        return redirect()->back()->withInput()->with('error', 'Error al guardar en base de datos.');
    }

    public function bitacora(): string
    {
        $busqueda   = trim((string) ($this->request->getGet('buscar') ?? ''));
        $fechaDesde = trim((string) ($this->request->getGet('fecha_desde') ?? ''));
        $fechaHasta = trim((string) ($this->request->getGet('fecha_hasta') ?? ''));

        $registros = $this->sopladoModel->filtrarBitacora(
            $busqueda !== '' ? $busqueda : null,
            $fechaDesde !== '' ? $fechaDesde : null,
            $fechaHasta !== '' ? $fechaHasta : null
        );

        $data = [
            'registros'      => $registros,
            'totalFiltrados' => count($registros),
            'totalGeneral'   => $this->sopladoModel->contarTotal(),
            'busqueda'       => $busqueda,
            'fechaDesde'     => $fechaDesde,
            'fechaHasta'     => $fechaHasta,
        ];

        return view('soplado/bitacora', $data);
    }

    public function exportar(): ResponseInterface
    {
        $busqueda   = trim((string) ($this->request->getGet('buscar') ?? ''));
        $fechaDesde = trim((string) ($this->request->getGet('fecha_desde') ?? ''));
        $fechaHasta = trim((string) ($this->request->getGet('fecha_hasta') ?? ''));

        $registros = $this->sopladoModel->filtrarBitacora(
            $busqueda !== '' ? $busqueda : null,
            $fechaDesde !== '' ? $fechaDesde : null,
            $fechaHasta !== '' ? $fechaHasta : null
        );

        $filename    = "Reporte_Soplado_CPUs_" . date('Ymd_His') . ".csv";
        $delimitador = ';';

        $headers = [
            'ID', 'Fecha/Hora', 'Analista', 'N° Traslado',
            'Placa ID', 'Energiza', 'Da Video', 'Detecta Disco',
            'Ingresó BIOS', 'Pasta Térmica', 'Gel Cucarachas', 'Contenido Máquina'
        ];

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM para apertura directa en Excel
        $output .= implode($delimitador, $headers) . "\r\n";

        foreach ($registros as $row) {
            $contenido = str_replace(["\r\n", "\r", "\n", '"'], [' ', ' ', ' ', '""'], (string)($row['maquina_contenia'] ?? '-'));

            $line = [
                $row['id'],
                $row['fecha_creacion'] ?? ($row['created_at'] ?? ''),
                '"' . str_replace('"', '""', (string)($row['nombre_analista'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['num_traslado'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['placa_id'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['energiza'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['da_video'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['detecta_disco'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['ingreso_bios'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['pasta_termica'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['gel_cucarachas'] ?? '-')) . '"',
                '"' . $contenido . '"',
            ];

            $output .= implode($delimitador, $line) . "\r\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($output);
    }
}