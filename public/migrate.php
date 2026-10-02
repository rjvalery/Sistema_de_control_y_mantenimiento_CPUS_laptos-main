<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=diagnostico_cpus;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Agregar la columna permisos si no existe
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'permisos'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE usuarios ADD permisos TEXT NULL AFTER rol");
        echo "<h1>Éxito</h1><p>Se ha añadido la columna 'permisos' a la tabla 'usuarios'.</p>";
    } else {
        echo "<h1>Información</h1><p>La columna 'permisos' ya existía en la tabla.</p>";
    }

    echo "<p>Por favor, cierra sesión, vuelve a entrar como administrador y revisa el panel de usuarios. Ya puedes eliminar este archivo <code>migrate.php</code>.</p>";

} catch (PDOException $e) {
    echo "<h1>Error de Base de Datos</h1><p>" . $e->getMessage() . "</p>";
}
