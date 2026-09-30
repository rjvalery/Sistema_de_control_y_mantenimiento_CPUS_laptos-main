<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EquipoModel;
use App\Models\UsuarioModel;
use App\Models\InventarioGeneralModel;
use App\Services\UploadService;
use CodeIgniter\HTTP\ResponseInterface;

class Equipos extends BaseController
{
    protected $equipoModel;
    protected $usuarioModel;
    protected $uploadService;
    protected $inventarioModel;

    public function __construct()
    {
        $this->equipoModel     = new EquipoModel();
        $this->usuarioModel    = new UsuarioModel();
        $this->uploadService   = new UploadService();
        $this->inventarioModel = new InventarioGeneralModel();
    }

    public function formulario()
    {
        $data['analistas'] = $this->usuarioModel->where('rol', 'analista')->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();
        return view('equipos/formulario', $data);
    }

    public function guardar(): ResponseInterface
    {
        $placaId     = (string) ($this->request->getPost('placa_id') ?? '');
        $tipoGestion = (string) ($this->request->getPost('tipo_gestion') ?? '');
        $file        = $this->request->getFile('foto_equipo');
        $tieneArchivo = $file && $file->isValid() && !$file->hasMoved();
        $esBaja      = ($tipoGestion === 'Baja');

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
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $this->validator->getError('foto_equipo') ?: 'Error en la validación de la imagen.'
            ]);
        }

        // Guardado seguro con nombre aleatorio en public/uploads/equipos/
        $fotoRuta = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/equipos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $nombreFoto = $file->getRandomName();
            $file->move($uploadDir, $nombreFoto);
            $fotoRuta = 'uploads/equipos/' . $nombreFoto;
        }

        $nombreAnalista = (session('usuario_rol') === 'analista')
            ? (string) session('usuario_nombre')
            : (string) ($this->request->getPost('nombre_analista') ?: session('usuario_nombre'));

        $serialDisco = trim((string) ($this->request->getPost('serial_disco') ?: $this->request->getPost('serial_disco_baja')));

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
            'descripcion_novedad' => $this->request->getPost('descripcion_novedad'),
            'motivo_baja'         => $this->request->getPost('motivo_baja'),
            'ubicacion_destino'   => $this->request->getPost('ubicacion_destino'),
            'foto_equipo'         => $fotoRuta,
            'fecha_creacion'      => date('Y-m-d H:i:s')
        ];

        if ($this->equipoModel->insert($data)) {
            // Sincronizar y descontar de pendientes en inventario general
            $this->inventarioModel->marcarIntervenido((string)$placaId, 'diagnostico', $nombreAnalista);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Guardado correctamente']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Error al guardar en base de datos']);
    }

    public function bitacora()
    {
        $busqueda   = trim($this->request->getGet('buscar') ?? '');
        $fechaDesde = trim($this->request->getGet('fecha_desde') ?? '');
        $fechaHasta = trim($this->request->getGet('fecha_hasta') ?? '');

        $builder = $this->equipoModel->builder();

        if ($busqueda !== '') {
            $builder->groupStart()
                    ->like('placa_id', $busqueda)
                    ->orLike('num_traslado', $busqueda)
                    ->orLike('nombre_analista', $busqueda)
                    ->groupEnd();
        }

        if ($fechaDesde !== '') {
            $builder->where('fecha_creacion >=', $fechaDesde . ' 00:00:00');
        }

        if ($fechaHasta !== '') {
            $builder->where('fecha_creacion <=', $fechaHasta . ' 23:59:59');
        }

        $registros = $builder->orderBy('id', 'DESC')->get()->getResultArray();

        $data['registros']      = $registros;
        $data['totalFiltrados'] = count($registros);
        $data['totalGeneral']   = (new EquipoModel())->countAllResults();
        $data['busqueda']       = $busqueda;
        $data['fechaDesde']     = $fechaDesde;
        $data['fechaHasta']     = $fechaHasta;

        return view('equipos/bitacora', $data);
    }

    public function exportar(): ResponseInterface
    {
        $busqueda   = trim($this->request->getGet('buscar') ?? '');
        $fechaDesde = trim($this->request->getGet('fecha_desde') ?? '');
        $fechaHasta = trim($this->request->getGet('fecha_hasta') ?? '');

        $builder = $this->equipoModel->builder();

        if ($busqueda !== '') {
            $builder->groupStart()
                    ->like('placa_id', $busqueda)
                    ->orLike('num_traslado', $busqueda)
                    ->orLike('nombre_analista', $busqueda)
                    ->groupEnd();
        }

        if ($fechaDesde !== '') {
            $builder->where('fecha_creacion >=', $fechaDesde . ' 00:00:00');
        }

        if ($fechaHasta !== '') {
            $builder->where('fecha_creacion <=', $fechaHasta . ' 23:59:59');
        }

        $registros = $builder->orderBy('id', 'DESC')->get()->getResultArray();
        $filename  = "Reporte_Equipos_Diagnostico_" . date('Ymd_His') . ".csv";
        $delimitador = ';';

        $headers = [
            'ID', 'Fecha/Hora', 'Analista', 'N° Traslado',
            'Placa ID', 'Gestión', 'Energiza', 'Da Video',
            'Estado Actual', 'Intervención', 'Origen Pieza', 'Novedad',
            'Motivo Baja', 'Serial Disco', 'Ubicación Destino'
        ];

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM para apertura directa en Excel
        $output .= implode($delimitador, $headers) . "\r\n";

        foreach ($registros as $row) {
            $novedad = str_replace(["\r\n", "\r", "\n", '"'], [' ', ' ', ' ', '""'], (string)($row['descripcion_novedad'] ?? '-'));
            $motivoBaja = str_replace(["\r\n", "\r", "\n", '"'], [' ', ' ', ' ', '""'], (string)($row['motivo_baja'] ?? '-'));

            $line = [
                $row['id'],
                $row['fecha_creacion'],
                '"' . str_replace('"', '""', (string)($row['nombre_analista'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['num_traslado'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['placa_id'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['tipo_gestion'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['energiza'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['da_video'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['estado_actual'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['que_va_intervenir'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['origen_pieza'] ?? '-')) . '"',
                '"' . $novedad . '"',
                '"' . $motivoBaja . '"',
                '"' . str_replace('"', '""', (string)($row['serial_disco'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['ubicacion_destino'] ?? '-')) . '"',
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