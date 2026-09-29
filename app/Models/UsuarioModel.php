<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table         = 'usuarios';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'usuario', 'password', 'rol', 'activo', 'created_at'];
    protected $useTimestamps = false;
    protected $returnType    = 'array';
}
