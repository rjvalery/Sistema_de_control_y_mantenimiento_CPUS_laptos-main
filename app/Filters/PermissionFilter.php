<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): \CodeIgniter\HTTP\ResponseInterface|string|null
    {
        if (!session()->has('usuario_id')) {
            return redirect()->to('/login');
        }

        // $arguments contiene los permisos requeridos (ej: ['ver_inventario', 'editar_equipos'])
        if (!empty($arguments)) {
            helper('auth');
            foreach ($arguments as $permiso) {
                if (has_permission($permiso)) {
                    return null; // Con un permiso de la lista que tenga, puede entrar
                }
            }
            // Si llega aquí es porque no tiene ninguno de los permisos requeridos
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos suficientes para acceder a este módulo.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): \CodeIgniter\HTTP\ResponseInterface|string|null
    {
        return null;
    }
}
