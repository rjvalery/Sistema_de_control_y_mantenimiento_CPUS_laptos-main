<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EquipoModel;
use App\Models\PortatilModel;
use App\Models\InventarioGeneralModel;
use App\Models\SopladoModel;
use App\Models\UsuarioModel;
use CodeIgniter\Model;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $usuarioModel    = new UsuarioModel();
        $totalEquipos    = $this->contarRegistros(new EquipoModel()) ?? 0;
        $totalSoplado    = $this->contarRegistros(new SopladoModel()) ?? 0;
        $totalPortatiles = $this->contarRegistros(new PortatilModel()) ?? 0;
        $totalAnalistas  = $usuarioModel->where('rol', 'analista')->countAllResults();
        $totalIntervenciones = $totalEquipos + $totalSoplado + $totalPortatiles;

        $inventarioModel = new InventarioGeneralModel();
        $statsInventario = $inventarioModel->obtenerEstadisticasInventario();

        $data = [
            'statsInventario'     => $statsInventario,
            'totalIntervenciones' => $totalIntervenciones,
            'totalEquipos'        => $totalEquipos,
            'totalSoplado'        => $totalSoplado,
            'totalPortatiles'     => $totalPortatiles,
            'totalAnalistas'      => $totalAnalistas,
            'usuarios'            => (new UsuarioModel())->orderBy('id', 'ASC')->findAll(),
        ];

        if (session('usuario_rol') === 'analista') {
            return view('dashboard/analista', $data);
        }

        return view('dashboard/index', $data);
    }

    private function contarRegistros(Model $model): ?int
    {
        try {
            return $model->countAllResults();
        } catch (\Throwable) {
            return null;
        }
    }
}

