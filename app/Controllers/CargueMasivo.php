<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\InventarioGeneralModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CargueMasivo extends BaseController
{
    protected InventarioGeneralModel $inventarioModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->inventarioModel = model(InventarioGeneralModel::class);
        $this->parserService   = new \App\Services\ExcelParserService();
    }

    /**
     * Muestra la interfaz de cargue masivo directo a la base de datos (inventario_general)
     * con panel de mapeo y localización de equipos en el sistema.
     */
    public function index(): string|ResponseInterface
    {
        if (!in_array(session('usuario_rol'), ['admin', 'analista'], true)) {
            return redirect()->to(base_url('login'))->with('error', 'Por favor inicia sesión para continuar.');
        }



        $busqueda = trim((string) $this->request->getGet('buscar'));
        $filtro   = trim((string) $this->request->getGet('filtro')); // 'agregados', 'pendientes', o vacío
        $traslado = trim((string) $this->request->getGet('traslado'));
        $limite   = (int) ($this->request->getGet('limite') ?? 250);
        if ($limite <= 0 || $limite > 1000) {
            $limite = 250;
        }

        $registros = $this->inventarioModel->filtrarInventario(
            $busqueda !== '' ? $busqueda : null,
            $filtro !== '' ? $filtro : null,
            $traslado !== '' ? $traslado : null,
            $limite
        );

        $totalFiltrados = count($registros);
        $statsInventario = $this->inventarioModel->obtenerEstadisticasInventario();
        $trasladosDisponibles = $this->inventarioModel->obtenerTrasladosRegistrados();

        $data = [
            'registros'            => $registros,
            'totalRegistros'       => $statsInventario['totalCargados'],
            'statsInventario'      => $statsInventario,
            'busqueda'             => $busqueda,
            'filtro'               => $filtro,
            'traslado'             => $traslado,
            'limite'               => $limite,
            'trasladosDisponibles' => $trasladosDisponibles,
            'totalFiltrados'       => $totalFiltrados,
            'esAdmin'              => (session('usuario_rol') === 'admin'),
        ];

        return view('cargue_masivo/index', $data);
    }

    /**
     * Ejecuta el mapeo de base de datos cruzando inventario_general con las tablas
     * equipos, soplado_registros y garantias_portatiles para localizar las ya agregadas.
     */
    public function sincronizar(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $traslado = trim((string) $this->request->getGet('traslado'));
        $targetTraslado = ($traslado !== '') ? $traslado : null;

        $resTraslado = $this->inventarioModel->mapearTraslado($targetTraslado);
        $resultado   = $this->inventarioModel->sincronizarConSistema();

        if ($targetTraslado !== null) {
            $msg = "Mapeo completado para traslado <strong>" . esc($targetTraslado) . "</strong>: {$resTraslado['intervenidos']} intervenidas de {$resTraslado['totalTraslado']} con estatus <strong>Cargado</strong>.";
            return redirect()->to(base_url('inventario?traslado=' . urlencode($targetTraslado)))->with('msg', $msg);
        }

        $msg = "Mapeo general completado: Se cruzaron todas las máquinas intervenidas registradas ({$resTraslado['intervenidos']} intervenidas de {$resTraslado['totalTraslado']}) con estatus <strong>Cargado</strong>.";
        return redirect()->to(base_url('inventario'))->with('msg', $msg);
    }

    /**
     * Mapea y recupera los números de traslado de las máquinas que llegaron sin traslado
     * (por respaldo de base de datos) cruzándolas con las bitácoras técnicas donde sí fueron capturados.
     */
    public function recuperarTraslados(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $res = $this->inventarioModel->recuperarTrasladosDesdeBitacoras();
        $msg = "Recuperación completada: Se asignó número de traslado a <strong>{$res['total_recuperados']}</strong> máquinas desde las bitácoras (Diagnóstico CPU: {$res['actualizados_equipos']}, Soplado: {$res['actualizados_soplado']}, Portátiles: {$res['actualizados_portatiles']}). Restantes sin traslado: {$res['restantes_sin_traslado']}.";

        return redirect()->to(base_url('inventario'))->with('msg', $msg);
    }

    /**
     * Descarga la plantilla CSV oficial con codificación UTF-8 basada en Formato en Cubic (8 columnas).
     */
    public function plantilla(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $delimitador = ';';
        $filename    = 'plantilla_cargue_formato_cubic.csv';

        $headers = [
            'Identificador 1',
            'Identificador 2',
            'Ref. Principal',
            'Descripción',
            'Zona Origen',
            'Ubicación Origen',
            'Verificado',
            'Observaciones'
        ];

        $ejemplos = [
            ['ACT-10021', 'SN-MBP99201', 'MacBook Pro 16 M1', 'Portátil corporativo Apple', 'Sede Central', 'Piso 3 - Operaciones', 'Verificado', 'Equipo en buen estado físico'],
            ['ACT-10022', 'SN-TC883011', 'ThinkCentre M70q', 'CPU de escritorio Lenovo', 'Sede Norte', 'Bodega 1 - Estante B', 'Pendiente', 'Requiere mantenimiento y soplado'],
        ];

        $output = "\xEF\xBB\xBF"; // UTF-8 BOM para compatibilidad total con Excel
        $output .= implode($delimitador, $headers) . "\r\n";
        foreach ($ejemplos as $row) {
            $output .= implode($delimitador, $row) . "\r\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($output);
    }

    /**
     * Procesa la importación masiva adaptada a las 8 columnas del Formato en Cubic con detección inteligente.
     */
    public function procesar(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }



        $numTraslado = trim((string) $this->request->getPost('num_traslado'));
        if ($numTraslado === '') {
            return redirect()->to(base_url('inventario'))->with('error', 'El número de traslado es obligatorio para confirmar y procesar la subida.');
        }

        $file = $this->request->getFile('archivo_csv');
        if (!$file || !$file->isValid()) {
            return redirect()->to(base_url('inventario'))->with('error', 'Por favor selecciona un archivo Excel (.xlsx) o CSV válido.');
        }

        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, ['csv', 'txt', 'xlsx', 'xls'], true)) {
            return redirect()->to(base_url('inventario'))->with('error', 'El archivo debe tener formato Excel (.xlsx) o delimitado (.csv / .txt).');
        }

        $realPath = $file->getTempName();

        $rawHeaders = [];
        $filasExcel = [];
        $handleCsv  = null;
        $delimitador = ',';

        if ($ext === 'xlsx' || $ext === 'xls') {
            try {
                if ($ext === 'xlsx' || $this->parserService->esArchivoZip($realPath)) {
                    $datosExcel = $this->parserService->extraerFilasDesdeExcel($realPath);
                } else {
                    $datosExcel = $this->parserService->extraerFilasDesdeHtmlXls($realPath);
                    if ($datosExcel === null) {
                        return redirect()->to(base_url('inventario'))->with('error', 'El archivo .xls tiene un formato binario antiguo. Por favor ábrelo en Excel y guárdalo como libro .xlsx o .csv.');
                    }
                }

                if (empty($datosExcel['headers'])) {
                    return redirect()->to(base_url('inventario'))->with('error', 'El archivo Excel no contiene encabezados válidos o está vacío.');
                }

                $rawHeaders = $datosExcel['headers'];
                $filasExcel = $datosExcel['rows'];
            } catch (\Throwable $e) {
                return redirect()->to(base_url('inventario'))->with('error', 'Error al procesar el archivo Excel: ' . $e->getMessage());
            }
        } else {
            // Manejo nativo de archivos CSV y TXT delimitados
            $handleCsv = fopen($realPath, 'r');
            if (!$handleCsv) {
                return redirect()->to(base_url('inventario'))->with('error', 'No se pudo abrir el archivo CSV para lectura.');
            }

            // Detectar y saltar BOM UTF-8 si existe
            $bom = fread($handleCsv, 3);
            $offset = ($bom === "\xEF\xBB\xBF") ? 3 : 0;
            fseek($handleCsv, $offset);

            // Detectar automáticamente el delimitador (; , \t |)
            $delimitador = $this->parserService->detectarDelimitador($realPath, $offset);

            // Leer primera línea (encabezados)
            $rawHeaders = fgetcsv($handleCsv, 0, $delimitador);
            if (!$rawHeaders || $this->parserService->filaEstaVacia($rawHeaders)) {
                fclose($handleCsv);
                return redirect()->to(base_url('inventario'))->with('error', 'El archivo no contiene encabezados válidos o está vacío.');
            }
        }

        // Normalizar encabezados (minúsculas, sin tildes, caracteres limpios)
        $headers = [];
        foreach ($rawHeaders as $index => $h) {
            $cleaned = $this->parserService->limpiarTextoUtf8((string)$h);
            $cleaned = mb_strtolower(trim($cleaned), 'UTF-8');
            $cleaned = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $cleaned);
            $cleaned = preg_replace('/[^a-z0-9_]/', '_', $cleaned);
            $headers[$index] = trim(preg_replace('/_+/', '_', $cleaned ?? ''), '_');
        }

        // Mapear qué columna de los encabezados corresponde a qué campo
        $mapColumnas = $this->parserService->identificarColumnas($headers, count($rawHeaders));

        $nombreArchivo = $file->getClientName();
        $usuarioCargue = (string) (session('usuario_nombre') ?? 'Administrador');
        $ahora = date('Y-m-d H:i:s');

        $insertados       = 0;
        $errores          = 0;
        $detallesErrores  = [];
        $numFilaFisica    = 1; // Fila 1 fueron los encabezados
        $batchData        = [];

        // Generador unificado de filas tanto para Excel como para CSV
        $iteradorFilas = function() use ($ext, $handleCsv, $delimitador, &$filasExcel) {
            if ($ext === 'csv' || $ext === 'txt') {
                if ($handleCsv) {
                    while (($r = fgetcsv($handleCsv, 0, $delimitador)) !== false) {
                        yield $r;
                    }
                    fclose($handleCsv);
                }
            } else {
                foreach ($filasExcel as $r) {
                    yield $r;
                }
                $filasExcel = []; // Liberar memoria
            }
        };

        foreach ($iteradorFilas() as $rawRow) {
            $numFilaFisica++;

            // Ignorar filas 100% vacías silenciosamente
            if ($this->parserService->filaEstaVacia($rawRow)) {
                continue;
            }

            // Convertir codificación a UTF-8 limpia por cada celda
            $row = [];
            foreach ($rawRow as $idx => $val) {
                $row[$idx] = trim($this->parserService->limpiarTextoUtf8((string)$val));
            }

            // Extraer valores según mapeo de columnas
            $id1             = $this->parserService->obtenerValorColumna($row, $mapColumnas['id1']);
            $id2             = $this->parserService->obtenerValorColumna($row, $mapColumnas['id2']);
            $refPrincipal    = $this->parserService->obtenerValorColumna($row, $mapColumnas['refPrincipal']);
            $descripcion     = $this->parserService->obtenerValorColumna($row, $mapColumnas['descripcion']);
            $zonaOrigen      = $this->parserService->obtenerValorColumna($row, $mapColumnas['zonaOrigen']);
            $ubicacionOrigen = $this->parserService->obtenerValorColumna($row, $mapColumnas['ubicacionOrigen']);
            $verificado      = $this->parserService->obtenerValorColumna($row, $mapColumnas['verificado']);
            $observaciones   = $this->parserService->obtenerValorColumna($row, $mapColumnas['observaciones']);

            // Se requiere obligatoriamente al menos un identificador real (Placa o Serial válido)
            // Si la fila no tiene ni placa ni serial (o son vacíos/guiones), se descarta de inmediato
            if (!$this->parserService->esIdentificadorValido($id1) && !$this->parserService->esIdentificadorValido($id2)) {
                continue;
            }

            // Omitir si la fila es una repetición de los encabezados (ej. 'Identificador 1', 'Serial', etc.)
            if ($this->parserService->esFilaEncabezado($id1, $id2, $refPrincipal, $descripcion)) {
                continue;
            }

            // Omitir si la fila es un total, resumen o pie de página (ej. 'Total: 21', 'Firma', etc.)
            if ($this->parserService->esFilaPieDePagina($id1, $id2, $refPrincipal, $descripcion)) {
                continue;
            }

            // Detectar número de traslado específico por fila si existe la columna en el archivo
            $trasladoFila = $this->parserService->obtenerValorColumna($row, $mapColumnas['numTraslado'] ?? null);
            $trasladoFinal = !empty($trasladoFila) ? $trasladoFila : $numTraslado;

            // Resolver valores derivados para asegurar compatibilidad con formularios/dashboard
            $placaDerivada = !empty($id1) ? $id1 : (!empty($id2) ? $id2 : (!empty($refPrincipal) ? $refPrincipal : 'SIN-PLACA'));
            $serialDerivado = !empty($id2) ? $id2 : (!empty($id1) ? $id1 : 'SIN-SERIAL');
            $tipoDerivado = !empty($descripcion) ? $descripcion : 'General';
            $modeloDerivado = !empty($refPrincipal) ? $refPrincipal : (!empty($descripcion) ? $descripcion : 'Sin especificar');
            $ubicacionDerivada = trim(($zonaOrigen ?? '') . ($ubicacionOrigen ? ' - ' . $ubicacionOrigen : ''), ' -');
            $estadoDerivado = !empty($verificado) ? $verificado : 'Cargado';

            // Ajustar longitudes máximas para evitar errores de longitud en MySQL
            $insertData = [
                'identificador_1'   => $id1 !== null && $id1 !== '' ? mb_substr($id1, 0, 100, 'UTF-8') : null,
                'identificador_2'   => $id2 !== null && $id2 !== '' ? mb_substr($id2, 0, 100, 'UTF-8') : null,
                'num_traslado'      => mb_substr($trasladoFinal, 0, 100, 'UTF-8'),
                'ref_principal'     => $refPrincipal !== null && $refPrincipal !== '' ? mb_substr($refPrincipal, 0, 150, 'UTF-8') : null,
                'descripcion'       => $descripcion !== null && $descripcion !== '' ? mb_substr($descripcion, 0, 255, 'UTF-8') : null,
                'zona_origen'       => $zonaOrigen !== null && $zonaOrigen !== '' ? mb_substr($zonaOrigen, 0, 100, 'UTF-8') : null,
                'ubicacion_origen'  => $ubicacionOrigen !== null && $ubicacionOrigen !== '' ? mb_substr($ubicacionOrigen, 0, 150, 'UTF-8') : null,
                'verificado'        => $verificado !== null && $verificado !== '' ? mb_substr($verificado, 0, 50, 'UTF-8') : null,
                'observaciones'     => $observaciones !== null && $observaciones !== '' ? $observaciones : null,
                'placa_id'          => mb_substr($placaDerivada, 0, 100, 'UTF-8'),
                'serial'            => mb_substr($serialDerivado, 0, 100, 'UTF-8'),
                'tipo_equipo'       => mb_substr($tipoDerivado, 0, 80, 'UTF-8'),
                'marca'             => null,
                'modelo'            => mb_substr($modeloDerivado, 0, 150, 'UTF-8'),
                'ubicacion'         => mb_substr($ubicacionDerivada, 0, 150, 'UTF-8'),
                'estado'            => mb_substr($estadoDerivado, 0, 80, 'UTF-8'),
                'archivo_origen'    => mb_substr($nombreArchivo, 0, 255, 'UTF-8'),
                'usuario_cargue'    => mb_substr($usuarioCargue, 0, 120, 'UTF-8'),
                'created_at'        => $ahora,
            ];

            $batchData[] = $insertData;

            if (count($batchData) >= 500) {
                try {
                    $this->inventarioModel->db->transStart();
                    $this->inventarioModel->insertBatch($batchData);
                    $this->inventarioModel->db->transComplete();
                    if ($this->inventarioModel->db->transStatus() === false) {
                        $errores += count($batchData);
                        $detallesErrores[] = "Error insertando lote de " . count($batchData) . " filas.";
                    } else {
                        $insertados += count($batchData);
                    }
                } catch (\Throwable $e) {
                    $errores += count($batchData);
                    if (count($detallesErrores) < 5) {
                        $detallesErrores[] = "Error en lote: " . $e->getMessage();
                    }
                }
                $batchData = [];
            }
        }

        if (count($batchData) > 0) {
            try {
                $this->inventarioModel->db->transStart();
                $this->inventarioModel->insertBatch($batchData);
                $this->inventarioModel->db->transComplete();
                if ($this->inventarioModel->db->transStatus() === false) {
                    $errores += count($batchData);
                    $detallesErrores[] = "Error insertando lote final de " . count($batchData) . " filas.";
                } else {
                    $insertados += count($batchData);
                }
            } catch (\Throwable $e) {
                $errores += count($batchData);
                if (count($detallesErrores) < 5) {
                    $detallesErrores[] = "Error en lote final: " . $e->getMessage();
                }
            }
        }

        if (is_resource($handleCsv)) {
            fclose($handleCsv);
        }

        // Construir mensaje de respuesta descriptivo
        if ($insertados > 0 && $errores === 0) {
            $msg = "Cargue masivo completado con éxito bajo el Traslado <strong>" . esc($numTraslado) . "</strong>. Se insertaron <strong>{$insertados}</strong> registros en <code>inventario_general</code>.";
            return redirect()->to(base_url('inventario'))->with('msg', $msg);
        }

        if ($insertados > 0 && $errores > 0) {
            $msg = "Cargue completado parcialmente bajo el Traslado <strong>" . esc($numTraslado) . "</strong>: Se insertaron <strong>{$insertados}</strong> registros. Hubo <strong>{$errores}</strong> fila(s) omitida(s) o con errores.";
            if (!empty($detallesErrores)) {
                $msg .= '<br><br><strong>Detalles detectados:</strong><ul class="mb-0 mt-1">';
                foreach ($detallesErrores as $det) {
                    $msg .= '<li>' . esc($det) . '</li>';
                }
                if ($errores > count($detallesErrores)) {
                    $msg .= '<li><em>... y ' . ($errores - count($detallesErrores)) . ' fila(s) adicionales con problema similar.</em></li>';
                }
                $msg .= '</ul>';
            }
            return redirect()->to(base_url('inventario'))->with('msg', $msg);
        }

        // Si 0 fueron insertados y hubo errores
        $errorMsg = "No se pudo insertar ningún registro ({$errores} filas fallidas o vacías).";
        if (!empty($detallesErrores)) {
            $errorMsg .= '<br><br><strong>Detalles:</strong><ul class="mb-0 mt-1">';
            foreach ($detallesErrores as $det) {
                $errorMsg .= '<li>' . esc($det) . '</li>';
            }
            $errorMsg .= '</ul>';
        }
        return redirect()->to(base_url('inventario'))->with('error', $errorMsg);
    }


}
