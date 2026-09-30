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

        $this->inventarioModel->asegurarTabla();

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

        $this->inventarioModel->asegurarTabla();

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
                if ($ext === 'xlsx' || $this->esArchivoZip($realPath)) {
                    $datosExcel = $this->extraerFilasDesdeExcel($realPath);
                } else {
                    $datosExcel = $this->extraerFilasDesdeHtmlXls($realPath);
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
            $delimitador = $this->detectarDelimitador($realPath, $offset);

            // Leer primera línea (encabezados)
            $rawHeaders = fgetcsv($handleCsv, 0, $delimitador);
            if (!$rawHeaders || $this->filaEstaVacia($rawHeaders)) {
                fclose($handleCsv);
                return redirect()->to(base_url('inventario'))->with('error', 'El archivo no contiene encabezados válidos o está vacío.');
            }
        }

        // Normalizar encabezados (minúsculas, sin tildes, caracteres limpios)
        $headers = [];
        foreach ($rawHeaders as $index => $h) {
            $cleaned = $this->limpiarTextoUtf8((string)$h);
            $cleaned = mb_strtolower(trim($cleaned), 'UTF-8');
            $cleaned = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $cleaned);
            $cleaned = preg_replace('/[^a-z0-9_]/', '_', $cleaned);
            $headers[$index] = trim(preg_replace('/_+/', '_', $cleaned ?? ''), '_');
        }

        // Mapear qué columna de los encabezados corresponde a qué campo
        $mapColumnas = $this->identificarColumnas($headers, count($rawHeaders));

        $nombreArchivo = $file->getClientName();
        $usuarioCargue = (string) (session('usuario_nombre') ?? 'Administrador');
        $ahora = date('Y-m-d H:i:s');

        $insertados       = 0;
        $errores          = 0;
        $detallesErrores  = [];
        $numFilaFisica    = 1; // Fila 1 fueron los encabezados

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
            if ($this->filaEstaVacia($rawRow)) {
                continue;
            }

            // Convertir codificación a UTF-8 limpia por cada celda
            $row = [];
            foreach ($rawRow as $idx => $val) {
                $row[$idx] = trim($this->limpiarTextoUtf8((string)$val));
            }

            // Extraer valores según mapeo de columnas
            $id1             = $this->obtenerValorColumna($row, $mapColumnas['id1']);
            $id2             = $this->obtenerValorColumna($row, $mapColumnas['id2']);
            $refPrincipal    = $this->obtenerValorColumna($row, $mapColumnas['refPrincipal']);
            $descripcion     = $this->obtenerValorColumna($row, $mapColumnas['descripcion']);
            $zonaOrigen      = $this->obtenerValorColumna($row, $mapColumnas['zonaOrigen']);
            $ubicacionOrigen = $this->obtenerValorColumna($row, $mapColumnas['ubicacionOrigen']);
            $verificado      = $this->obtenerValorColumna($row, $mapColumnas['verificado']);
            $observaciones   = $this->obtenerValorColumna($row, $mapColumnas['observaciones']);

            // Se requiere obligatoriamente al menos un identificador real (Placa o Serial válido)
            // Si la fila no tiene ni placa ni serial (o son vacíos/guiones), se descarta de inmediato
            if (!$this->esIdentificadorValido($id1) && !$this->esIdentificadorValido($id2)) {
                continue;
            }

            // Omitir si la fila es una repetición de los encabezados (ej. 'Identificador 1', 'Serial', etc.)
            if ($this->esFilaEncabezado($id1, $id2, $refPrincipal, $descripcion)) {
                continue;
            }

            // Omitir si la fila es un total, resumen o pie de página (ej. 'Total: 21', 'Firma', etc.)
            if ($this->esFilaPieDePagina($id1, $id2, $refPrincipal, $descripcion)) {
                continue;
            }

            // Detectar número de traslado específico por fila si existe la columna en el archivo
            $trasladoFila = $this->obtenerValorColumna($row, $mapColumnas['numTraslado'] ?? null);
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

            try {
                if ($this->inventarioModel->insert($insertData)) {
                    $insertados++;
                } else {
                    $errores++;
                    $dbErrors = $this->inventarioModel->errors();
                    $msgErr = !empty($dbErrors) ? implode(', ', $dbErrors) : 'Error de validación en base de datos.';
                    if (count($detallesErrores) < 5) {
                        $detallesErrores[] = "Fila {$numFilaFisica}: {$msgErr}";
                    }
                }
            } catch (\Throwable $e) {
                $errores++;
                if (count($detallesErrores) < 5) {
                    $detallesErrores[] = "Fila {$numFilaFisica}: " . $e->getMessage();
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

    /**
     * Detecta de forma inteligente el delimitador más probable del archivo (, ; \t |).
     */
    private function detectarDelimitador(string $filePath, int $offset = 0): string
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ';';
        }

        fseek($handle, $offset);
        $delimitadores = [';', ',', "\t", '|'];
        $conteos = [';' => 0, ',' => 0, "\t" => 0, '|' => 0];

        $lineasLeidas = 0;
        while ($lineasLeidas < 5 && ($linea = fgets($handle)) !== false) {
            if (trim($linea) === '') {
                continue;
            }
            $lineasLeidas++;
            foreach ($delimitadores as $d) {
                $cols = str_getcsv($linea, $d);
                if (count($cols) > 1) {
                    $conteos[$d] += count($cols);
                }
            }
        }
        fclose($handle);

        arsort($conteos);
        $mejorDelimitador = key($conteos);

        return ($conteos[$mejorDelimitador] > 0) ? $mejorDelimitador : ';';
    }

    /**
     * Mapea encabezados a nombres de campos canónicos usando sinónimos y fallback posicional.
     */
    private function identificarColumnas(array $headers, int $totalColumnas): array
    {
        $sinonimos = [
            'id1' => [
                'identificador_1', 'identificador1', 'identificador', 'id_1', 'id1', 'id',
                'placa_id', 'placa', 'placa_inventario', 'activo', 'activo_fijo', 'codigo',
                'codigo_activo', 'num_inventario', 'item'
            ],
            'id2' => [
                'identificador_2', 'identificador2', 'id_2', 'id2', 'serial', 'serie',
                'sn', 's_n', 'serial_number', 'numero_serie', 'no_serie', 'num_serie', 'nro_serie'
            ],
            'refPrincipal' => [
                'ref_principal', 'refprincipal', 'referencia', 'ref', 'modelo', 'modelo_referencia',
                'referencia_principal', 'marca_modelo'
            ],
            'descripcion' => [
                'descripcion', 'tipo_equipo', 'equipo', 'detalle', 'descripcion_del_equipo',
                'tipo', 'categoria', 'nombre', 'especificacion'
            ],
            'zonaOrigen' => [
                'zona_origen', 'zonaorigen', 'zona', 'sede', 'bodega', 'sucursal', 'ciudad',
                'regional', 'area'
            ],
            'ubicacionOrigen' => [
                'ubicacion_origen', 'ubicacionorigen', 'ubicacion', 'puesto', 'oficina',
                'departamento', 'sitio', 'lugar'
            ],
            'verificado' => [
                'verificado', 'estado', 'estatus', 'status', 'condicion', 'situacion'
            ],
            'observaciones' => [
                'observaciones', 'observacion', 'notas', 'nota', 'comentario', 'comentarios',
                'detalle_adicional'
            ],
            'numTraslado' => [
                'num_traslado', 'numtraslado', 'numero_traslado', 'numerotraslado',
                'traslado', 'n_traslado', 'no_traslado', 'nro_traslado', 'guia', 'remision',
                'no_guia', 'num_guia', 'n_guia', 'orden_traslado'
            ],
        ];

        $mapeadas = [];

        // Primero buscar por coincidencia de nombre/sinónimo
        foreach ($sinonimos as $campo => $aliasList) {
            $encontrado = null;
            foreach ($headers as $idx => $nombreColumna) {
                if (in_array($nombreColumna, $aliasList, true)) {
                    $encontrado = $idx;
                    break;
                }
            }
            $mapeadas[$campo] = $encontrado;
        }

        // Fallback posicional si no se reconocieron encabezados pero tiene el orden estándar Cubic (8 columnas)
        if ($mapeadas['id1'] === null && $mapeadas['id2'] === null && $mapeadas['refPrincipal'] === null) {
            $mapeadas['id1']             = isset($headers[0]) ? 0 : null;
            $mapeadas['id2']             = isset($headers[1]) ? 1 : null;
            $mapeadas['refPrincipal']    = isset($headers[2]) ? 2 : null;
            $mapeadas['descripcion']     = isset($headers[3]) ? 3 : null;
            $mapeadas['zonaOrigen']      = isset($headers[4]) ? 4 : null;
            $mapeadas['ubicacionOrigen'] = isset($headers[5]) ? 5 : null;
            $mapeadas['verificado']      = isset($headers[6]) ? 6 : null;
            $mapeadas['observaciones']   = isset($headers[7]) ? 7 : null;
        }

        return $mapeadas;
    }

    /**
     * Obtiene el valor de una columna si está definida en la fila.
     */
    private function obtenerValorColumna(array $row, ?int $colIndex): ?string
    {
        if ($colIndex !== null && isset($row[$colIndex])) {
            $val = trim((string)$row[$colIndex]);
            return ($val !== '') ? $val : null;
        }
        return null;
    }

    /**
     * Asegura que el texto esté en UTF-8 válido convirtiendo desde ANSI/Windows-1252 si es necesario.
     */
    private function limpiarTextoUtf8(string $texto): string
    {
        if (!mb_check_encoding($texto, 'UTF-8')) {
            $convertido = @mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
            if ($convertido !== false) {
                $texto = $convertido;
            }
        }
        // Remover caracteres nulos o no imprimibles
        return str_replace("\0", '', $texto);
    }

    /**
     * Permite vaciar la tabla inventario_general.
     */
    public function vaciar(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $this->inventarioModel->asegurarTabla();
        $this->inventarioModel->truncate();

        return redirect()->to(base_url('inventario'))->with('msg', 'Los registros de inventario_general han sido vaciados correctamente.');
    }

    private function filaEstaVacia(array $row): bool
    {
        foreach ($row as $val) {
            if (trim((string)$val) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Comprueba si el archivo proporcionado tiene firma binaria de archivo ZIP (PK\x03\x04).
     */
    private function esArchivoZip(string $filePath): bool
    {
        $f = @fopen($filePath, 'rb');
        if (!$f) {
            return false;
        }
        $bytes = fread($f, 4);
        fclose($f);
        return str_starts_with($bytes, "PK\x03\x04");
    }

    /**
     * Extrae filas y encabezados de un archivo .xlsx nativo (OpenXML) usando ZipArchive y SimpleXML.
     * Lee directamente xl/sharedStrings.xml y xl/worksheets/sheet1.xml sin requerir Composer.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>}
     */
    private function extraerFilasDesdeExcel(string $filePath): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('La extensión PHP ZipArchive no está habilitada en el servidor.');
        }

        $zip = new \ZipArchive();
        $status = $zip->open($filePath);
        if ($status !== true) {
            throw new \RuntimeException('No se pudo abrir el archivo Excel como paquete ZIP (código: ' . $status . ').');
        }

        // 1. Cargar cadenas compartidas (sharedStrings.xml) si existen
        $sharedStrings = [];
        $sstXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sstXml !== false) {
            $xmlSst = @simplexml_load_string($sstXml);
            if ($xmlSst && isset($xmlSst->si)) {
                foreach ($xmlSst->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $textRun = '';
                        foreach ($si->r as $r) {
                            $textRun .= (string)($r->t ?? '');
                        }
                        $sharedStrings[] = $textRun;
                    } else {
                        $sharedStrings[] = (string)$si;
                    }
                }
            }
        }

        // 2. Localizar la primera hoja (sheet1.xml o similar)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', (string)$name)) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if ($sheetXml === false) {
            throw new \RuntimeException('No se encontró ninguna hoja de cálculo válida en el archivo Excel.');
        }

        $xmlSheet = @simplexml_load_string($sheetXml);
        if (!$xmlSheet || !isset($xmlSheet->sheetData) || !isset($xmlSheet->sheetData->row)) {
            return ['headers' => [], 'rows' => []];
        }

        $rawHeaders = [];
        $rows = [];
        $todasLasFilas = [];

        foreach ($xmlSheet->sheetData->row as $row) {
            $fila = [];
            $maxCol = -1;

            if (isset($row->c)) {
                foreach ($row->c as $c) {
                    $r = (string)$c['r'];
                    $t = (string)$c['t'];

                    // Extraer letra de columna (ej. A, B, AC)
                    $colIndex = null;
                    if (preg_match('/^([A-Za-z]+)(\d+)$/', $r, $matches)) {
                        $colIndex = $this->columnaExcelAIndice($matches[1]);
                    }

                    $valor = '';
                    if ($t === 's') {
                        // Índice en sharedStrings
                        $idx = (int)$c->v;
                        $valor = $sharedStrings[$idx] ?? '';
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $valor = (string)$c->is->t;
                    } elseif (isset($c->v)) {
                        $valor = (string)$c->v;
                    }

                    if ($colIndex !== null) {
                        $fila[$colIndex] = $valor;
                        if ($colIndex > $maxCol) {
                            $maxCol = $colIndex;
                        }
                    } else {
                        $fila[] = $valor;
                        $maxCol = count($fila) - 1;
                    }
                }
            }

            // Normalizar el arreglo para no dejar huecos de celdas intermedias vacías
            $filaNormalizada = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $filaNormalizada[$i] = $fila[$i] ?? '';
            }

            if (!$this->filaEstaVacia($filaNormalizada)) {
                $todasLasFilas[] = $filaNormalizada;
            }
        }

        if (empty($todasLasFilas)) {
            return ['headers' => [], 'rows' => []];
        }

        // Detectar de forma inteligente qué fila contiene los encabezados reales
        $headerIndex = 0;
        $palabrasEncabezado = [
            'identificador', 'placa', 'serial', 'serie', 'referencia', 'ref',
            'descripcion', 'equipo', 'zona', 'ubicacion', 'verificado',
            'observaciones', 'activo', 'codigo', 'item', 'traslado'
        ];

        foreach ($todasLasFilas as $idx => $f) {
            $coincidencias = 0;
            foreach ($f as $celda) {
                $norm = mb_strtolower(trim((string)$celda), 'UTF-8');
                foreach ($palabrasEncabezado as $palabra) {
                    if (str_contains($norm, $palabra)) {
                        $coincidencias++;
                        break;
                    }
                }
            }
            if ($coincidencias >= 1) {
                $headerIndex = $idx;
                break;
            }
        }

        $rawHeaders = $todasLasFilas[$headerIndex];
        $rows = array_slice($todasLasFilas, $headerIndex + 1);

        return [
            'headers' => $rawHeaders,
            'rows'    => $rows,
        ];
    }

    /**
     * Convierte letras de columna de Excel (A, B, ..., Z, AA, AB) a índice numérico 0-based.
     */
    private function columnaExcelAIndice(string $colStr): int
    {
        $colStr = strtoupper(trim($colStr));
        $len = strlen($colStr);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($colStr[$i]) - 64);
        }
        return max(0, $num - 1);
    }

    /**
     * Procesa archivos .xls que no son ZIP (por ejemplo, exportaciones HTML table o XML Spreadsheet 2003 de ERPs).
     * Retorna null si es un binario puro OLE2 BIFF.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, string>>}|null
     */
    private function extraerFilasDesdeHtmlXls(string $filePath): ?array
    {
        $contenido = @file_get_contents($filePath);
        if ($contenido === false || $contenido === '') {
            return ['headers' => [], 'rows' => []];
        }

        // Si comienza con la firma binaria de Microsoft OLE2 BIFF8: D0 CF 11 E0 A1 B1 1A E1
        if (str_starts_with($contenido, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) {
            return null;
        }

        // 1. Revisar si es XML Spreadsheet 2003 (<Workbook ... <Worksheet> ...)
        if (stripos($contenido, '<Workbook') !== false && stripos($contenido, '<Table') !== false) {
            $xml = @simplexml_load_string($contenido);
            if ($xml) {
                $tablas = $xml->xpath('//Table') ?: $xml->xpath('//*[local-name()="Table"]');
                if (!empty($tablas)) {
                    $rawHeaders = [];
                    $rows = [];
                    $esPrimera = true;

                    $filas = $tablas[0]->xpath('.//Row') ?: $tablas[0]->xpath('.//*[local-name()="Row"]');
                    foreach ($filas as $filaXml) {
                        $celdas = $filaXml->xpath('.//Cell') ?: $filaXml->xpath('.//*[local-name()="Cell"]');
                        $fila = [];
                        foreach ($celdas as $celdaXml) {
                            $datos = $celdaXml->xpath('.//Data') ?: $celdaXml->xpath('.//*[local-name()="Data"]');
                            $fila[] = !empty($datos) ? trim((string)$datos[0]) : '';
                        }

                        if ($esPrimera) {
                            $rawHeaders = $fila;
                            $esPrimera = false;
                        } else {
                            $rows[] = $fila;
                        }
                    }

                    return ['headers' => $rawHeaders, 'rows' => $rows];
                }
            }
        }

        // 2. Revisar si es una tabla HTML (<table ... <tr> ... <td>)
        if (stripos($contenido, '<table') !== false && stripos($contenido, '<tr') !== false) {
            $dom = new \DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($contenido, 'HTML-ENTITIES', 'UTF-8'));
            $filasDom = $dom->getElementsByTagName('tr');

            $rawHeaders = [];
            $rows = [];
            $esPrimera = true;

            foreach ($filasDom as $tr) {
                $fila = [];
                $celdas = $tr->getElementsByTagName('th');
                if ($celdas->length === 0) {
                    $celdas = $tr->getElementsByTagName('td');
                }
                foreach ($celdas as $td) {
                    $fila[] = trim($td->textContent);
                }

                if ($esPrimera) {
                    $rawHeaders = $fila;
                    $esPrimera = false;
                } else {
                    $rows[] = $fila;
                }
            }

            return ['headers' => $rawHeaders, 'rows' => $rows];
        }

        // 3. Fallback: archivo de texto plano delimitado guardado con extensión .xls
        $delimitador = (strpos($contenido, "\t") !== false) ? "\t" : ((strpos($contenido, ';') !== false) ? ';' : ',');
        $lineas = explode("\n", $contenido);
        $rawHeaders = [];
        $rows = [];
        $esPrimera = true;

        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if ($linea === '') {
                continue;
            }
            $cols = str_getcsv($linea, $delimitador);
            if ($esPrimera) {
                $rawHeaders = $cols;
                $esPrimera = false;
            } else {
                $rows[] = $cols;
            }
        }

        return ['headers' => $rawHeaders, 'rows' => $rows];
    }

    /**
     * Verifica si una fila corresponde a los encabezados o títulos de las columnas.
     */
    private function esFilaEncabezado(?string $id1, ?string $id2, ?string $ref, ?string $desc): bool
    {
        $palabrasClave = [
            'identificador', 'identificador 1', 'identificador_1', 'identificador1',
            'identificador 2', 'identificador_2', 'identificador2',
            'placa', 'placa id', 'placa_id', 'serial', 'serie', 'sn', 's/n',
            'ref. principal', 'ref_principal', 'referencia', 'descripcion',
            'zona origen', 'ubicacion origen', 'verificado', 'observaciones',
            'codigo', 'activo', 'activo fijo', 'item', 'no', 'nro'
        ];

        $v1 = mb_strtolower(trim((string)$id1), 'UTF-8');
        $v2 = mb_strtolower(trim((string)$id2), 'UTF-8');
        $v3 = mb_strtolower(trim((string)$ref), 'UTF-8');

        return in_array($v1, $palabrasClave, true)
            || in_array($v2, $palabrasClave, true)
            || in_array($v3, $palabrasClave, true)
            || ($v1 === 'identificador 1' && $v2 === 'identificador 2');
    }

    /**
     * Verifica si una fila corresponde a totales, firmas o pie de página del archivo.
     */
    private function esFilaPieDePagina(?string $id1, ?string $id2, ?string $ref, ?string $desc): bool
    {
        $textos = array_filter([$id1, $id2, $ref, $desc]);
        foreach ($textos as $t) {
            $tNorm = mb_strtolower(trim((string)$t), 'UTF-8');
            if (
                str_starts_with($tNorm, 'total') ||
                str_starts_with($tNorm, 'subtotal') ||
                str_starts_with($tNorm, 'cantidad') ||
                str_starts_with($tNorm, 'resumen') ||
                str_starts_with($tNorm, 'firma') ||
                str_starts_with($tNorm, 'recibido') ||
                str_starts_with($tNorm, 'entregado')
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Valida si un valor corresponde a un identificador real (Placa o Serial) y no a un texto vacío o comodín.
     */
    private function esIdentificadorValido(?string $valor): bool
    {
        if ($valor === null) {
            return false;
        }
        $v = trim($valor);
        if ($v === '' || mb_strlen($v, 'UTF-8') < 2) {
            return false;
        }
        $invalidos = [
            '-', '--', '---', '—', 'n/a', 'na', 'null', 'none', 's/n', 's.n',
            'sin serial', 'sin placa', 's/p', 'no aplica', 'nd', '0',
            'placa', 'serial', 'identificador', 'identificador 1', 'identificador 2'
        ];
        return !in_array(mb_strtolower($v, 'UTF-8'), $invalidos, true);
    }
}
