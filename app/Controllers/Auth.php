<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function index()
    {
        return view('auth/login');
    }

    public function authenticate()
    {
        $usuario  = trim((string) $this->request->getPost('usuario'));
        $password = (string) $this->request->getPost('password');

        if ($usuario === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Ingrese usuario y contraseña.');
        }

        $user = (new UsuarioModel())
            ->where('usuario', $usuario)
            ->where('activo', 1)
            ->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Credenciales inválidas.');
        }

        session()->regenerate();
        session()->set([
            'usuario_id'     => (int) $user['id'],
            'usuario_nombre' => $user['nombre'],
            'usuario_user'   => $user['usuario'],
            'usuario_rol'    => $user['rol'],
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('login'));
    }
}
