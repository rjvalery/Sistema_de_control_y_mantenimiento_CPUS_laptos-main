<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\InventarioGeneralModel;
use CodeIgniter\HTTP\ResponseInterface;

class Inventario extends BaseController
{
    protected InventarioGeneralModel $inventarioModel;

    public function __construct()
    {
        $this->inventarioModel = new InventarioGeneralModel();
    }

    /**
     * Endpoint API para consultar y sincronizar datos de un equipo en tiempo real al tipear la placa o serial.
     */
    public function buscarEquipo(): ResponseInterface
    {
        $query = trim((string) ($this->request->getGet('query') ?? $this->request->getGet('termino')));

        if ($query === '' || strlen($query) < 3) {
            return $this->response->setJSON([
                'encontrado' => false,
                'mensaje'    => 'Término de búsqueda muy corto (mínimo 3 caracteres).',
            ]);
        }

        $equipo = $this->inventarioModel->buscarPorTermino($query);

        // Si se encuentra en inventario general, asegurar traer el número de traslado (de inventario o de bitácoras)
        if ($equipo) {
            $numTraslado = $this->inventarioModel->buscarTrasladoEnSistema($query, $equipo);

            return $this->response->setJSON([
                'encontrado' => true,
                'equipo'     => [
                    'id'                    => (int) $equipo['id'],
                    'placa_id'              => $equipo['placa_id'],
                    'serial'                => $equipo['serial'],
                    'num_traslado'          => $numTraslado,
                    'tipo_equipo'           => $equipo['tipo_equipo'],
                    'marca'                 => $equipo['marca'],
                    'modelo'                => $equipo['modelo'],
                    'ubicacion'             => $equipo['ubicacion'],
                    'estado'                => $equipo['estado'],
                    'intervenido'           => (int) ($equipo['intervenido'] ?? 0) === 1,
                    'fecha_intervencion'    => $equipo['fecha_intervencion'],
                    'modulo_intervencion'   => $equipo['modulo_intervencion'],
                    'analista_intervencion' => $equipo['analista_intervencion'],
                    'origen_datos'          => 'inventario_general',
                ],
            ]);
        }

        // Si no está en inventario masivo, buscar si ya fue registrado previamente en el sistema
        $historial = $this->inventarioModel->buscarEnHistorialSistema($query);
        if ($historial) {
            return $this->response->setJSON([
                'encontrado' => true,
                'equipo'     => $historial,
            ]);
        }

        return $this->response->setJSON([
            'encontrado' => false,
            'mensaje'    => 'Equipo no registrado previamente ni en cargue masivo.',
        ]);
    }
}
