<?php

namespace App\Controllers;

use App\Models\PortatilModel;
use App\Models\UsuarioModel;
use App\Models\InventarioGeneralModel;
use App\Services\UploadService;
use CodeIgniter\HTTP\ResponseInterface;

class Portatiles extends BaseController
{
    protected $portatilModel;
    protected $usuarioModel;
    protected $uploadService;
    protected $inventarioModel;

    public function __construct()
    {
        $this->portatilModel   = new PortatilModel();
        $this->usuarioModel    = new UsuarioModel();
        $this->uploadService   = new UploadService();
        $this->inventarioModel = new InventarioGeneralModel();
    }

    public function formulario()
    {
        $data['analistas'] = $this->usuarioModel->where('rol', 'analista')->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();
        return view('portatiles/formulario', $data);
    }

    public function guardar()
    {
        $placaId  = (string) $this->request->getPost('placa_id_equipo');
        $file     = $this->request->getFile('foto_equipo');
        $fotoRuta = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fotoRuta = $this->uploadService->guardarEvidencia($file, $placaId, 'portatil');
        }

        $isAjax = $this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        $nombreAnalista = (session('usuario_rol') === 'analista')
            ? (string) session('usuario_nombre')
            : (string) ($this->request->getPost('nombre_analista') ?: session('usuario_nombre'));

        $estadoActual = (string) $this->request->getPost('estado_actual_equipo');

        $rawPiezas = $this->request->getPost('indique_pieza');
        $rawFrus   = $this->request->getPost('indique_fru');

        if (is_array($rawPiezas)) {
            $piezasLimpias = array_values(array_filter(array_map('trim', $rawPiezas), fn($v) => $v !== ''));
            $indiquePieza = !empty($piezasLimpias) ? implode(', ', $piezasLimpias) : null;
        } else {
            $indiquePieza = trim((string) $rawPiezas) ?: null;
        }

        if (is_array($rawFrus)) {
            $frusLimpios = array_values(array_filter(array_map('trim', $rawFrus), fn($v) => $v !== ''));
            $indiqueFru = !empty($frusLimpios) ? implode(', ', $frusLimpios) : null;
        } else {
            $indiqueFru = trim((string) $rawFrus) ?: null;
        }

        $reparadoPor   = (string) ($this->request->getPost('reparado_por') ?? '');
        $comentarioRep = trim((string) ($this->request->getPost('comentario_reparado') ?? ''));

        if ($estadoActual === 'Reparado') {
            $diagnosticoFinal = $reparadoPor ? "[$reparadoPor] $comentarioRep" : $comentarioRep;
        } else {
            $diagnosticoFinal = $this->request->getPost('diagnostico_laptop_intervenido');
        }

        $data = [
            'nombre_analista'                => $nombreAnalista,
            'numero_traslado'                => $this->request->getPost('numero_traslado'),
            'placa_id_equipo'                => $placaId,
            'tipo_gestion'                   => $this->request->getPost('tipo_gestion'),
            'energiza'                       => $this->request->getPost('energiza'),
            'da_video'                       => $this->request->getPost('da_video'),
            'realizo_test_lenovo'            => $this->request->getPost('realizo_test_lenovo'),
            'estado_actual_equipo'           => $estadoActual,
            'diagnostico_laptop_intervenido' => $diagnosticoFinal,
            'garantia'                       => ($estadoActual === 'Garantia') ? 'Aplica' : ($this->request->getPost('garantia') ?: null),
            'porque_solicita_garantia'       => $this->request->getPost('porque_solicita_garantia'),
            'numero_ticket'                  => $this->request->getPost('numero_ticket'),
            'estado_final_equipo'            => ($estadoActual === 'Reparado' && $reparadoPor) ? "Reparado por $reparadoPor" : ($estadoActual === 'Donacion' ? 'Donación' : $this->request->getPost('estado_final_equipo')),
            'indique_pieza'                  => $indiquePieza,
            'indique_fru'                    => $indiqueFru,
            'pieza_intervenida'              => $this->request->getPost('pieza_intervenida'),
            'origen_pieza'                   => ($estadoActual === 'Reparado' && $reparadoPor) ? $reparadoPor : $this->request->getPost('origen_pieza'),
            'motivo_baja'                    => $this->request->getPost('motivo_baja'),
            'serial_disco'                   => $this->request->getPost('serial_disco'),
            'created_at'                     => date('Y-m-d H:i:s')
        ];

        if ($fotoRuta) {
            $data['foto_ruta'] = $fotoRuta;
        }

        try {
            if ($this->portatilModel->insert($data)) {
                // Sincronizar y descontar de pendientes en inventario general
                $this->inventarioModel->marcarIntervenido($placaId, 'portatil', $nombreAnalista);

                if ($isAjax) {
                    return $this->response->setJSON([
                        'status'   => 'success',
                        'message'  => 'Registro de diagnóstico guardado correctamente.',
                        'placa_id' => $placaId
                    ]);
                }
                return redirect()->to(base_url('portatiles/formulario'))->with('msg', 'Registro de portátiles guardado correctamente.');
            }

            if ($isAjax) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Error al guardar en la base de datos.']);
            }

            return redirect()->back()->withInput()->with('error', 'Error al guardar en base de datos.');
        } catch (\Throwable $e) {
            log_message('error', 'Error al guardar portátil: ' . $e->getMessage());
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Error en el servidor: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function evidencia()
    {
        $placa = trim((string) ($this->request->getGet('placa') ?? ''));
        $data = [
            'placaInicial' => $placa,
            'analistas'    => $this->usuarioModel->where('rol', 'analista')->where('activo', 1)->orderBy('nombre', 'ASC')->findAll()
        ];
        return view('portatiles/evidencia', $data);
    }

    public function buscarLaptop()
    {
        $query = trim((string) ($this->request->getGet('query') ?? ''));
        if ($query === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Término vacío']);
        }

        $laptop = $this->portatilModel->where('placa_id_equipo', $query)->orderBy('id', 'DESC')->first();
        $inv    = $this->inventarioModel->buscarPorTermino($query);

        return $this->response->setJSON([
            'status'      => 'success',
            'diagnostico' => $laptop,
            'inventario'  => $inv,
            'tiene_foto'  => !empty($laptop['foto_ruta']) && file_exists((string) $laptop['foto_ruta'])
        ]);
    }

    public function guardarEvidencia()
    {
        $placaId = trim((string) $this->request->getPost('placa_id_equipo'));
        $file    = $this->request->getFile('foto_equipo');
        $isAjax  = $this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        if ($placaId === '') {
            $msg = 'La Placa ID o Serial del portátil es obligatoria.';
            return $isAjax ? $this->response->setJSON(['status' => 'error', 'message' => $msg]) : redirect()->back()->withInput()->with('error', $msg);
        }

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            $msg = 'La foto de evidencia es obligatoria o el archivo no es válido.';
            return $isAjax ? $this->response->setJSON(['status' => 'error', 'message' => $msg]) : redirect()->back()->withInput()->with('error', $msg);
        }

        $fotoRuta = $this->uploadService->guardarEvidencia($file, $placaId, 'portatil');
        if (!$fotoRuta) {
            $msg = 'No se pudo guardar la fotografía en el servidor.';
            return $isAjax ? $this->response->setJSON(['status' => 'error', 'message' => $msg]) : redirect()->back()->withInput()->with('error', $msg);
        }

        $nombreAnalista = (session('usuario_rol') === 'analista')
            ? (string) session('usuario_nombre')
            : (string) ($this->request->getPost('nombre_analista') ?: session('usuario_nombre'));

        try {
            // Actualizar el registro existente de la laptop o crear uno base si aún no se había diagnosticado
            $laptop = $this->portatilModel->where('placa_id_equipo', $placaId)->orderBy('id', 'DESC')->first();
            if ($laptop) {
                $this->portatilModel->update($laptop['id'], ['foto_ruta' => $fotoRuta]);
            } else {
                $this->portatilModel->insert([
                    'nombre_analista' => $nombreAnalista,
                    'placa_id_equipo' => $placaId,
                    'tipo_gestion'    => 'Diagnóstico',
                    'foto_ruta'       => $fotoRuta,
                    'created_at'      => date('Y-m-d H:i:s')
                ]);
            }

            // Marcar también como intervenido en el inventario general
            $this->inventarioModel->marcarIntervenido($placaId, 'portatil', $nombreAnalista);

            if ($isAjax) {
                return $this->response->setJSON([
                    'status'    => 'success',
                    'message'   => 'Evidencia fotográfica del portátil guardada correctamente.',
                    'foto_ruta' => $fotoRuta,
                    'placa_id'  => $placaId
                ]);
            }

            return redirect()->to(base_url('portatiles/evidencia'))->with('msg', 'Evidencia fotográfica guardada correctamente.');
        } catch (\Throwable $e) {
            log_message('error', 'Error al guardar evidencia de portátil: ' . $e->getMessage());
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Error al guardar evidencia: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function bitacora()
    {
        $busqueda   = trim($this->request->getGet('buscar') ?? '');
        $fechaDesde = trim($this->request->getGet('fecha_desde') ?? '');
        $fechaHasta = trim($this->request->getGet('fecha_hasta') ?? '');

        $builder = $this->portatilModel->builder();

        if ($busqueda !== '') {
            $builder->groupStart()
                    ->like('placa_id_equipo', $busqueda)
                    ->orLike('numero_ticket', $busqueda)
                    ->orLike('nombre_analista', $busqueda)
                    ->groupEnd();
        }

        if ($fechaDesde !== '') {
            $builder->where('created_at >=', $fechaDesde . ' 00:00:00');
        }

        if ($fechaHasta !== '') {
            $builder->where('created_at <=', $fechaHasta . ' 23:59:59');
        }

        $data['registros']  = $builder->orderBy('id', 'DESC')->get()->getResultArray();
        $data['busqueda']   = $busqueda;
        $data['fechaDesde'] = $fechaDesde;
        $data['fechaHasta'] = $fechaHasta;

        return view('portatiles/bitacora', $data);
    }

    public function exportar(): ResponseInterface
    {
        $busqueda   = trim($this->request->getGet('buscar') ?? '');
        $fechaDesde = trim($this->request->getGet('fecha_desde') ?? '');
        $fechaHasta = trim($this->request->getGet('fecha_hasta') ?? '');

        $builder = $this->portatilModel->builder();

        if ($busqueda !== '') {
            $builder->groupStart()
                    ->like('placa_id_equipo', $busqueda)
                    ->orLike('numero_ticket', $busqueda)
                    ->orLike('nombre_analista', $busqueda)
                    ->groupEnd();
        }

        if ($fechaDesde !== '') {
            $builder->where('created_at >=', $fechaDesde . ' 00:00:00');
        }

        if ($fechaHasta !== '') {
            $builder->where('created_at <=', $fechaHasta . ' 23:59:59');
        }

        $registros = $builder->orderBy('id', 'DESC')->get()->getResultArray();
        $filename  = "Reporte_Portatiles_Garantias_" . date('Ymd_His') . ".csv";
        $delimitador = ';';

        $headers = [
            'ID', 'Fecha', 'Analista', 'N° Traslado', 'Placa ID',
            'Gestión', 'Energiza', 'Da Video', 'Test Lenovo', 'Estado Actual',
            'Diagnóstico', 'Garantía', 'N° Ticket', 'Razón Garantía',
            'Estado Final', 'Pieza', 'FRU', 'Pieza Intervenida',
            'Origen Pieza', 'Serial Disco', 'Motivo Baja'
        ];

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM para apertura directa en Excel
        $output .= implode($delimitador, $headers) . "\r\n";

        foreach ($registros as $row) {
            $diagnostico = str_replace(["\r\n", "\r", "\n", '"'], [' ', ' ', ' ', '""'], (string)($row['diagnostico_laptop_intervenido'] ?? '-'));
            $razon = str_replace(["\r\n", "\r", "\n", '"'], [' ', ' ', ' ', '""'], (string)($row['porque_solicita_garantia'] ?? '-'));
            $motivoBaja = str_replace(["\r\n", "\r", "\n", '"'], [' ', ' ', ' ', '""'], (string)($row['motivo_baja'] ?? '-'));

            $line = [
                $row['id'],
                $row['created_at'] ?? '',
                '"' . str_replace('"', '""', (string)($row['nombre_analista'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['numero_traslado'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['placa_id_equipo'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['tipo_gestion'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string)($row['energiza'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['da_video'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['realizo_test_lenovo'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['estado_actual_equipo'] ?? '-')) . '"',
                '"' . $diagnostico . '"',
                '"' . str_replace('"', '""', (string)($row['garantia'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['numero_ticket'] ?? '-')) . '"',
                '"' . $razon . '"',
                '"' . str_replace('"', '""', (string)($row['estado_final_equipo'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['indique_pieza'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['indique_fru'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['pieza_intervenida'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['origen_pieza'] ?? '-')) . '"',
                '"' . str_replace('"', '""', (string)($row['serial_disco'] ?? '-')) . '"',
                '"' . $motivoBaja . '"',
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