<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'usuario' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'rol' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'admin',
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('usuario');
        $this->forge->createTable('usuarios', true);

        $this->db->table('usuarios')->insert([
            'nombre'     => 'Administrador',
            'usuario'    => 'admin',
            'password'   => password_hash('Admin123', PASSWORD_DEFAULT),
            'rol'        => 'admin',
            'activo'     => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('usuarios', true);
    }
}
