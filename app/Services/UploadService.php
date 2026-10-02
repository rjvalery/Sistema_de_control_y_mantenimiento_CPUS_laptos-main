<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

class UploadService
{
    /**
     * Sube y organiza evidencias en C:\Users\LENOVO\Pictures\fotos\[categoria]\[AÑO]\[MES_TEXTO]\[DIA]\
     */
    public function guardarEvidencia(?UploadedFile $file, string $placaId, string $categoria = 'diagnostico'): ?string
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $rawDir = ($categoria === 'diagnostico')
            ? (env('app.rutaDiagnosticos') ?: env('uploads.diagnostico', WRITEPATH . 'uploads/diagnostico'))
            : env("uploads.{$categoria}", WRITEPATH . 'uploads/' . $categoria);

        $baseDir = rtrim(str_replace('\\', '/', (string) $rawDir), '/');

        $meses = [
            '01' => 'Enero',      '02' => 'Febrero',   '03' => 'Marzo',
            '04' => 'Abril',      '05' => 'Mayo',      '06' => 'Junio',
            '07' => 'Julio',      '08' => 'Agosto',    '09' => 'Septiembre',
            '10' => 'Octubre',    '11' => 'Noviembre', '12' => 'Diciembre'
        ];

        $anio      = date('Y');
        $nombreMes = $meses[date('m')];
        $dia       = date('d');

        $targetDir = "{$baseDir}/{$anio}/{$nombreMes}/{$dia}/";

        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                log_message('error', "UploadService: No se pudo crear el directorio '{$targetDir}'.");
                return null;
            }
        }

        $ext = $file->getClientExtension() ?: 'jpg';
        $placaLimpia = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($placaId));
        if (empty($placaLimpia)) {
            $placaLimpia = 'evidencia_' . date('His');
        }

        $fileName = $placaLimpia . '.' . $ext;

        if (file_exists($targetDir . $fileName)) {
            $fileName = $placaLimpia . '_' . date('His') . '.' . $ext;
        }

        $destPath = $targetDir . $fileName;

        try {
            $file->move($targetDir, $fileName);
            // Optimización de respaldo en servidor si el archivo recibido supera 1.5MB
            $this->optimizarSiEsPesado($destPath);
            return $destPath;
        } catch (\Throwable $e) {
            log_message('error', "UploadService: Error moviendo archivo a '{$destPath}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Respaldo para optimizar fotos en servidor si superan 1.5MB sin compresión previa
     */
    private function optimizarSiEsPesado(string $filePath): void
    {
        if (!file_exists($filePath) || filesize($filePath) < 1.5 * 1024 * 1024) {
            return;
        }

        if (!function_exists('imagecreatefromstring')) {
            return;
        }

        try {
            $data = file_get_contents($filePath);
            if (!$data) {
                return;
            }

            $src = @imagecreatefromstring($data);
            if (!$src) {
                return;
            }

            $width  = imagesx($src);
            $height = imagesy($src);
            $maxDim = 1200;

            if ($width > $maxDim || $height > $maxDim) {
                if ($width > $height) {
                    $newWidth  = $maxDim;
                    $newHeight = (int) round(($height * $maxDim) / $width);
                } else {
                    $newHeight = $maxDim;
                    $newWidth  = (int) round(($width * $maxDim) / $height);
                }

                $dst = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($dst, $filePath, 75);
                imagedestroy($dst);
            } else {
                imagejpeg($src, $filePath, 75);
            }

            imagedestroy($src);
        } catch (\Throwable $e) {
            // No interrumpir el flujo si falla el redimensionamiento GD
        }
    }
}