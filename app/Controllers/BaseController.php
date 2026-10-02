<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Helpers cargados para todos los controladores que extienden BaseController.
     */
    protected $helpers = ['url', 'form', 'auth'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
    }

    /**
     * Retorna una respuesta JSON estandarizada de éxito.
     */
    protected function respondSuccess(array $data = [], string $message = '', int $code = 200): ResponseInterface
    {
        $payload = array_merge(['status' => 'success'], $data);
        if ($message !== '') {
            $payload['message'] = $message;
        }

        return $this->response->setStatusCode($code)->setJSON($payload);
    }

    /**
     * Retorna una respuesta JSON estandarizada de error.
     */
    protected function respondError(string $message, int $code = 400, array $errors = []): ResponseInterface
    {
        $payload = [
            'status'  => 'error',
            'message' => $message,
        ];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return $this->response->setStatusCode($code)->setJSON($payload);
    }

    /**
     * Retorna una respuesta de descarga CSV estandarizada (con BOM UTF-8).
     */
    protected function exportarCsvResponse(array $headers, array $data, string $filename, string $delimiter = ';'): ResponseInterface
    {
        $output = "\xEF\xBB\xBF"; // UTF-8 BOM
        $output .= implode($delimiter, $headers) . "\r\n";
        
        foreach ($data as $row) {
            $cleanedRow = array_map(function ($val) {
                if ($val === null) return '';
                // Limpiar saltos de línea y escapar comillas dobles
                $val = str_replace(["\r\n", "\r", "\n", '"'], [' ', ' ', ' ', '""'], (string)$val);
                return '"' . $val . '"';
            }, $row);
            
            $output .= implode($delimiter, $cleanedRow) . "\r\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setBody($output);
    }
}
