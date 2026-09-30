<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Usuarios extends BaseController
{
    protected UsuarioModel $usuarioModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->usuarioModel = model(UsuarioModel::class);
    }

    /**
     * Muestra la vista principal del módulo de gestión de usuarios y asignación de roles.
     */
    public function index(): ResponseInterface|string
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado: solo administradores pueden acceder al módulo de usuarios.');
        }

        $busqueda  = trim((string) $this->request->getGet('buscar'));
        $filtroRol = trim((string) $this->request->getGet('rol'));

        $usuarios       = $this->usuarioModel->filtrarUsuarios($busqueda !== '' ? $busqueda : null, $filtroRol !== '' ? $filtroRol : null);
        $totalUsuarios  = $this->usuarioModel->countAllResults();
        $totalAdmins    = $this->usuarioModel->contarPorRol('admin');
        $totalAnalistas = $this->usuarioModel->contarPorRol('analista');

        $data = [
            'usuarios'       => $usuarios,
            'totalUsuarios'  => $totalUsuarios,
            'totalAdmins'    => $totalAdmins,
            'totalAnalistas' => $totalAnalistas,
            'busqueda'       => $busqueda,
            'filtroRol'      => $filtroRol,
        ];

        return view('usuarios/index', $data);
    }

    /**
     * Asigna o cambia el rol de un usuario del sistema (solo accesible por administradores).
     */
    public function cambiarRol(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permisos para modificar roles.');
        }

        $rules = [
            'id'  => 'required|is_natural_no_zero',
            'rol' => 'required|in_list[admin,analista]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('usuarios'))->with('error', 'El rol o usuario seleccionado no es válido.');
        }

        $id       = (int) $this->request->getPost('id');
        $nuevoRol = trim((string) $this->request->getPost('rol'));

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return redirect()->to(base_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        // Evitar que el administrador actual se degrade a analista a sí mismo por error
        if ((int) session('usuario_id') === $id && $nuevoRol !== 'admin') {
            return redirect()->to(base_url('usuarios'))->with('error', 'No puedes quitarte el rol de administrador a ti mismo.');
        }

        $this->usuarioModel->update($id, ['rol' => $nuevoRol]);

        return redirect()->to(base_url('usuarios'))->with('msg', "Rol de '{$usuario['nombre']}' actualizado a '{$nuevoRol}' correctamente.");
    }

    /**
     * Crea un nuevo usuario con rol asignado desde el módulo de usuarios.
     */
    public function crear(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permisos para crear usuarios.');
        }

        $rules = [
            'nombre'   => 'required|min_length[3]|max_length[120]',
            'usuario'  => 'required|min_length[3]|max_length[60]|is_unique[usuarios.usuario]',
            'password' => 'required|min_length[6]|max_length[255]',
            'rol'      => 'required|in_list[admin,analista]',
        ];

        if (!$this->validate($rules)) {
            $errores = implode(' ', $this->validator->getErrors());
            return redirect()->to(base_url('usuarios'))->with('error', $errores);
        }

        $password = (string) $this->request->getPost('password');

        $this->usuarioModel->insert([
            'nombre'     => trim((string) $this->request->getPost('nombre')),
            'usuario'    => trim((string) $this->request->getPost('usuario')),
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'rol'        => (string) $this->request->getPost('rol'),
            'activo'     => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('usuarios'))->with('msg', 'Nuevo usuario creado exitosamente con su rol asignado.');
    }

    /**
     * Restablece la contraseña de un usuario a una clave indicada o por defecto (función de administrador).
     */
    public function resetPassword(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permisos para restablecer contraseñas.');
        }

        $rules = [
            'id'             => 'required|is_natural_no_zero',
            'nueva_password' => 'permit_empty|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('usuarios'))->with('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
        }

        $id      = (int) $this->request->getPost('id');
        $usuario = $this->usuarioModel->find($id);

        if (!$usuario) {
            return redirect()->to(base_url('usuarios'))->with('error', 'Usuario no encontrado.');
        }

        $nuevaPassword = trim((string) $this->request->getPost('nueva_password'));
        if ($nuevaPassword === '') {
            $nuevaPassword = 'Password123*';
        }

        $this->usuarioModel->update($id, [
            'password' => password_hash($nuevaPassword, PASSWORD_BCRYPT),
        ]);

        return redirect()->to(base_url('usuarios'))->with('msg', "Contraseña de '{$usuario['nombre']}' ({$usuario['usuario']}) restablecida con éxito a: {$nuevaPassword}");
    }

    /**
     * Permite al usuario/analista en sesión cambiar su propia contraseña.
     */
    public function cambiarPasswordPropia(): ResponseInterface
    {
        $idUsuario = (int) session('usuario_id');
        if ($idUsuario <= 0) {
            return redirect()->to(base_url('login'))->with('error', 'Sesión expirada.');
        }

        $usuario = $this->usuarioModel->find($idUsuario);
        if (!$usuario) {
            return redirect()->to(base_url('login'))->with('error', 'Usuario no encontrado.');
        }

        $rules = [
            'password_actual'    => 'required',
            'password_nueva'     => 'required|min_length[6]',
            'password_confirmar' => 'required|matches[password_nueva]',
        ];

        if (!$this->validate($rules)) {
            $primerError = current($this->validator->getErrors()) ?: 'Error en la validación de contraseñas.';
            return redirect()->back()->with('error', $primerError);
        }

        $passwordActual = (string) $this->request->getPost('password_actual');
        $passwordNueva  = (string) $this->request->getPost('password_nueva');

        if (!password_verify($passwordActual, (string) $usuario['password'])) {
            return redirect()->back()->with('error', 'La contraseña actual ingresada es incorrecta.');
        }

        $this->usuarioModel->update($idUsuario, [
            'password' => password_hash($passwordNueva, PASSWORD_BCRYPT),
        ]);

        return redirect()->back()->with('msg', 'Tu contraseña ha sido actualizada con éxito.');
    }
}
