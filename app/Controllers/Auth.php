<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Auth extends BaseController
{
    protected UsuarioModel $usuarioModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->usuarioModel = model(UsuarioModel::class);
    }

    public function index(): string
    {
        return view('auth/login');
    }

    public function authenticate(): ResponseInterface
    {
        $rules = [
            'usuario'  => 'required|min_length[3]|max_length[60]',
            'password' => 'required|min_length[4]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Por favor ingrese un usuario y contraseña válidos.');
        }

        // Protección contra fuerza bruta (Rate Limiting)
        // Permite un máximo de 5 intentos por cada 5 minutos desde la misma IP
        $throttler = \Config\Services::throttler();
        $ipAddress = $this->request->getIPAddress();
        
        if ($throttler->check('login_attempt_' . $ipAddress, 5, 5 * MINUTE) === false) {
            return redirect()->back()->withInput()->with('error', 'Demasiados intentos fallidos. Por seguridad, por favor espera 5 minutos antes de volver a intentarlo.');
        }

        $usuario  = trim((string) $this->request->getPost('usuario'));
        $password = (string) $this->request->getPost('password');

        $user = $this->usuarioModel->buscarActivoPorUsuario($usuario);

        if (!$user || !password_verify($password, (string) $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Credenciales inválidas.');
        }

        session()->regenerate();
        session()->set([
            'usuario_id'     => (int) $user['id'],
            'usuario_nombre' => $user['nombre'],
            'usuario_user'   => $user['usuario'],
            'usuario_rol'    => $user['rol'],
            'usuario_permisos' => $user['permisos'],
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    public function logout(): ResponseInterface
    {
        session()->destroy();

        return redirect()->to(site_url('login'));
    }
}
