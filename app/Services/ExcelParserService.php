<?php

namespace App\Services;

class ExcelParserService
{
    /**
     * Detecta de forma inteligente el delimitador más probable del archivo (, ; \t |).
     */
    public function detectarDelimitador(string $filePath, int $offset = 0): string
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
    public function identificarColumnas(array $headers, int $totalColumnas): array
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
    public function obtenerValorColumna(array $row, ?int $colIndex): ?string
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
    public function limpiarTextoUtf8(string $texto): string
    {
        if (!mb_check_encoding($texto, 'UTF-8')) {
            $convertido = @mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
            if ($convertido !== false) {
                $texto = $convertido;
            }
        }
        return str_replace("\0", '', $texto);
    }

    public function filaEstaVacia(array $row): bool
    {
        foreach ($row as $val) {
            if (trim((string)$val) !== '') {
                return false;
            }
        }
        return true;
    }

    public function esArchivoZip(string $filePath): bool
    {
        $f = @fopen($filePath, 'rb');
        if (!$f) {
            return false;
        }
        $bytes = fread($f, 4);
        fclose($f);
        return str_starts_with($bytes, "PK\x03\x04");
    }

    public function extraerFilasDesdeExcel(string $filePath): array
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

    public function extraerFilasDesdeHtmlXls(string $filePath): ?array
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

    public function esFilaEncabezado(?string $id1, ?string $id2, ?string $ref, ?string $desc): bool
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

    public function esFilaPieDePagina(?string $id1, ?string $id2, ?string $ref, ?string $desc): bool
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

    public function esIdentificadorValido(?string $valor): bool
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
