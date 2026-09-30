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
    protected $helpers = ['url', 'form'];

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
}
