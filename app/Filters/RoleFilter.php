<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): \CodeIgniter\HTTP\ResponseInterface|string|null
    {
        if (! session()->has('usuario_id')) {
            return redirect()->to('/login');
        }

        if (empty($arguments)) {
            return null;
        }

        $rol = session('usuario_rol');
        if (! in_array($rol, $arguments, true)) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): \CodeIgniter\HTTP\ResponseInterface|string|null
    {
        return null;
    }
}
