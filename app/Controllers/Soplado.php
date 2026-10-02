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
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        try {
            if ($this->sopladoModel->insert($data)) {
                // Sincronizar y descontar de pendientes en inventario general
                $this->inventarioModel->marcarIntervenido((string)$placaId, 'soplado', $nombreAnalista);

                if ($isAjax) {
                    return $this->respondSuccess([], 'Registro y evidencia guardados correctamente.');
                }
                return redirect()->to(base_url('soplado/formulario'))->with('msg', 'Registro y evidencia guardados correctamente.');
            }

            $errores = $this->sopladoModel->errors();
            $msgError = !empty($errores) ? implode(', ', $errores) : 'Verifique los datos ingresados.';

            if ($isAjax) {
                return $this->respondError('Error de validación al guardar: ' . $msgError);
            }

            return redirect()->back()->withInput()->with('error', 'Error al guardar en base de datos.');
        } catch (\Throwable $e) {
            log_message('error', 'Error al registrar soplado: ' . $e->getMessage());
            $msg = 'Error al registrar en la base de datos: ' . $e->getMessage();
            return $isAjax ? $this->respondError($msg) : redirect()->back()->withInput()->with('error', $msg);
        }
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

        $filename = "Reporte_Soplado_CPUs_" . date('Ymd_His') . ".csv";
        $headers = [
            'ID', 'Fecha/Hora', 'Analista', 'N° Traslado',
            'Placa ID', 'Energiza', 'Da Video', 'Detecta Disco',
            'Ingresó BIOS', 'Pasta Térmica', 'Gel Cucarachas', 'Contenido Máquina'
        ];

        $data = [];
        foreach ($registros as $row) {
            $data[] = [
                $row['id'],
                $row['created_at'] ?? ($row['fecha_creacion'] ?? ''),
                $row['nombre_analista'] ?? '',
                $row['num_traslado'] ?? '',
                $row['placa_id'] ?? '',
                $row['energiza'] ?? '-',
                $row['da_video'] ?? '-',
                $row['detecta_disco'] ?? '-',
                $row['ingreso_bios'] ?? '-',
                $row['pasta_termica'] ?? '-',
                $row['gel_cucarachas'] ?? '-',
                $row['maquina_contenia'] ?? '-'
            ];
        }

        return $this->exportarCsvResponse($headers, $data, $filename);
    }
}