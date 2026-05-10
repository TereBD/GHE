<?php
/**
 * Asistente de instalación desde CLI.
 * Uso: php database/setup.php
 * Crea/recrea la BD, tablas y datos de ejemplo con un hash de contraseña fresco.
 */

declare(strict_types=1);

$config = require __DIR__ . '/../config.php';
$db = $config['db'];

try {
    // 1. Crear BD si no existe
    $pdoSinDb = new PDO(
        "mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}",
        $db['username'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdoSinDb->exec("CREATE DATABASE IF NOT EXISTS `{$db['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdoSinDb->exec("USE `{$db['database']}`");

    // 2. Ejecutar schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    // Quitar CREATE DATABASE y USE porque ya estamos conectados
    $schema = preg_replace('/CREATE DATABASE.*?;/i', '', $schema);
    $schema = preg_replace('/USE\s+.*?;/i', '', $schema);
    $pdoSinDb->exec($schema);

    // 3. Ejecutar seed (sin la parte de usuarios)
    $seed = file_get_contents(__DIR__ . '/seed.sql');
    // Quitar USE
    $seed = preg_replace('/USE\s+.*?;/i', '', $seed);
    $pdoSinDb->exec($seed);

    // 4. Crear usuario admin con hash fresco
    $hash = password_hash('password', PASSWORD_BCRYPT);
    $stmt = $pdoSinDb->prepare(
        "INSERT INTO usuarios (usuario, password) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE password = VALUES(password)"
    );
    $stmt->execute(['admin', $hash]);

    echo "OK. Base de datos '{$db['database']}' lista.\n";
    echo "Usuario: admin / password\n";

    $pdoSinDb = null;
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
