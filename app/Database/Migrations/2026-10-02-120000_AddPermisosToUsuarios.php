<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPermisosToUsuarios extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('usuarios', [
            'permisos' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'rol'
            ]
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('usuarios', 'permisos');
    }
}
