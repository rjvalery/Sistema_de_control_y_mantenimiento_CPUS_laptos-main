<?php

declare(strict_types=1);

$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "[OK] Conexión con MySQL en Laragon establecida.\n";

    $sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'database.sql';
    if (!file_exists($sqlFile)) {
        throw new RuntimeException("Archivo database.sql no encontrado.");
    }

    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    echo "[OK] Base de datos 'diagnostico_cpus' y todas las tablas creadas exitosamente.\n";

    // Verificar tablas creadas
    $pdo->exec("USE `diagnostico_cpus`;");
    $stmt = $pdo->query("SHOW TABLES;");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "[INFO] Tablas presentes en diagnostico_cpus:\n";
    foreach ($tablas as $t) {
        echo "  - {$t}\n";
    }

    // Verificar usuario admin
    $stmtAdmin = $pdo->query("SELECT id, nombre, usuario, rol, activo FROM usuarios;");
    $usuarios = $stmtAdmin->fetchAll(PDO::FETCH_ASSOC);
    echo "[INFO] Usuarios en el sistema:\n";
    foreach ($usuarios as $u) {
        echo "  - ID: {$u['id']} | Usuario: {$u['usuario']} | Rol: {$u['rol']}\n";
    }

} catch (PDOException $e) {
    echo "[ERROR PDO]: " . $e->getMessage() . "\n";
    exit(1);
} catch (Throwable $e) {
    echo "[ERROR]: " . $e->getMessage() . "\n";
    exit(1);
}
