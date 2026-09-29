<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class Usuarios extends BaseController
{
    protected UsuarioModel $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Asigna o cambia el rol de un usuario del sistema (solo accesible por administradores).
     */
    public function cambiarRol(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permisos para modificar roles.');
        }

        $id = (int) $this->request->getPost('id');
        $nuevoRol = trim((string) $this->request->getPost('rol'));

        $rolesPermitidos = ['admin', 'analista'];
        if (!in_array($nuevoRol, $rolesPermitidos, true)) {
            return redirect()->to(base_url('dashboard'))->with('error', 'El rol seleccionado no es válido.');
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Usuario no encontrado.');
        }

        // Evitar que el administrador actual se degrade a analista a sí mismo por error
        if ((int) session('usuario_id') === $id && $nuevoRol !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'No puedes quitarte el rol de administrador a ti mismo.');
        }

        $this->usuarioModel->update($id, ['rol' => $nuevoRol]);

        return redirect()->to(base_url('dashboard'))->with('msg', "Rol de '{$usuario['nombre']}' actualizado a '{$nuevoRol}' correctamente.");
    }

    /**
     * Crea un nuevo usuario con rol asignado desde el dashboard.
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
            return redirect()->to(base_url('dashboard'))->with('error', $errores);
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

        return redirect()->to(base_url('dashboard'))->with('msg', 'Nuevo usuario creado exitosamente con su rol asignado.');
    }

    /**
     * Restablece la contraseña de un usuario a una clave indicada o por defecto (función de administrador).
     */
    public function resetPassword(): ResponseInterface
    {
        if (session('usuario_rol') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permisos para restablecer contraseñas.');
        }

        $id = (int) $this->request->getPost('id');
        $usuario = $this->usuarioModel->find($id);

        if (!$usuario) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Usuario no encontrado.');
        }

        $nuevaPassword = trim((string) $this->request->getPost('nueva_password'));
        if ($nuevaPassword === '') {
            $nuevaPassword = 'Password123*';
        }

        if (strlen($nuevaPassword) < 6) {
            return redirect()->to(base_url('dashboard'))->with('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
        }

        $this->usuarioModel->update($id, [
            'password' => password_hash($nuevaPassword, PASSWORD_BCRYPT),
        ]);

        return redirect()->to(base_url('dashboard'))->with('msg', "Contraseña de '{$usuario['nombre']}' ({$usuario['usuario']}) restablecida con éxito a: {$nuevaPassword}");
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

        $passwordActual    = (string) $this->request->getPost('password_actual');
        $passwordNueva     = (string) $this->request->getPost('password_nueva');
        $passwordConfirmar = (string) $this->request->getPost('password_confirmar');

        if (!password_verify($passwordActual, (string) $usuario['password'])) {
            return redirect()->back()->with('error', 'La contraseña actual ingresada es incorrecta.');
        }

        if (strlen($passwordNueva) < 6) {
            return redirect()->back()->with('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
        }

        if ($passwordNueva !== $passwordConfirmar) {
            return redirect()->back()->with('error', 'La nueva contraseña y su confirmación no coinciden.');
        }

        $this->usuarioModel->update($idUsuario, [
            'password' => password_hash($passwordNueva, PASSWORD_BCRYPT),
        ]);

        return redirect()->back()->with('msg', 'Tu contraseña ha sido actualizada con éxito.');
    }
}
