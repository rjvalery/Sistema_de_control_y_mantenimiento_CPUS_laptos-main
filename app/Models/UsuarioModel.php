<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = ['nombre', 'usuario', 'password', 'rol', 'activo', 'created_at'];

    protected $validationRules  = [
        'nombre'   => 'required|min_length[3]|max_length[120]',
        'usuario'  => 'required|min_length[3]|max_length[60]|is_unique[usuarios.usuario,id,{id}]',
        'rol'      => 'required|in_list[admin,analista]',
        'activo'   => 'permit_empty|in_list[0,1]',
    ];

    /**
     * Busca un usuario activo por su nombre de usuario (para login).
     */
    public function buscarActivoPorUsuario(string $usuario): ?array
    {
        return $this->where('usuario', $usuario)
                    ->where('activo', 1)
                    ->first();
    }

    /**
     * Retorna la lista de analistas activos ordenados alfabéticamente.
     */
    public function obtenerAnalistasActivos(): array
    {
        return $this->where('rol', 'analista')
                    ->where('activo', 1)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Filtra los usuarios del sistema por término de búsqueda y rol.
     */
    public function filtrarUsuarios(?string $busqueda = null, ?string $filtroRol = null): array
    {
        $builder = $this->builder();

        if ($filtroRol !== null && in_array($filtroRol, ['admin', 'analista'], true)) {
            $builder->where('rol', $filtroRol);
        }

        if ($busqueda !== null && trim($busqueda) !== '') {
            $termino = trim($busqueda);
            $builder->groupStart()
                    ->like('nombre', $termino)
                    ->orLike('usuario', $termino)
                    ->groupEnd();
        }

        return $builder->orderBy('id', 'ASC')->get()->getResultArray();
    }

    /**
     * Cuenta usuarios según su rol.
     */
    public function contarPorRol(string $rol): int
    {
        return (int) $this->builder()->where('rol', $rol)->countAllResults();
    }
}
