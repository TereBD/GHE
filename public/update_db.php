<?php
/**
 * Migración desde navegador.
 * Uso: http://localhost/GHE/public/update_db.php
 */

declare(strict_types=1);

$config = require __DIR__ . '/../config.php';
$db = $config['db'];

$mensajes = [];
$error = false;

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}",
        $db['username'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 1. Añadir columna dias_excluidos si no existe
    $stmt = $pdo->query("SHOW COLUMNS FROM docentes LIKE 'dias_excluidos'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE docentes ADD COLUMN dias_excluidos VARCHAR(100) DEFAULT NULL AFTER horas_proyecto");
        $mensajes[] = 'OK: Columna dias_excluidos añadida.';
    } else {
        $mensajes[] = 'OK: Columna dias_excluidos ya existía.';
    }

    // 2. Crear tabla indisponibilidades si no existe (VARCHAR para compatibilidad MySQL 5.x)
    $pdo->exec("CREATE TABLE IF NOT EXISTS indisponibilidades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        docente_id INT NOT NULL,
        dia_semana VARCHAR(20) NOT NULL,
        sesion INT NOT NULL,
        UNIQUE KEY uniq_indisp (docente_id, dia_semana, sesion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    try {
        $pdo->exec("ALTER TABLE indisponibilidades ADD CONSTRAINT fk_indisp_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE");
    } catch (Throwable $e) {
        $mensajes[] = 'Aviso: FK no añadida (no crítico): ' . $e->getMessage();
    }
    $mensajes[] = 'OK: Tabla indisponibilidades lista.';

    // 3. Crear tabla preasignaciones si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS preasignaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        docente_id INT NOT NULL,
        grupo_id INT NOT NULL,
        asignatura_id INT NOT NULL,
        dia_semana VARCHAR(20) NOT NULL,
        sesion INT NOT NULL,
        UNIQUE KEY uniq_docente_slot (docente_id, dia_semana, sesion),
        UNIQUE KEY uniq_grupo_slot (grupo_id, dia_semana, sesion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    try {
        $pdo->exec("ALTER TABLE preasignaciones ADD CONSTRAINT fk_preasig_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE");
    } catch (Throwable $e) {
        $mensajes[] = 'Aviso: FK docente no añadida: ' . $e->getMessage();
    }
    try {
        $pdo->exec("ALTER TABLE preasignaciones ADD CONSTRAINT fk_preasig_grupo FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE");
    } catch (Throwable $e) {
        $mensajes[] = 'Aviso: FK grupo no añadida: ' . $e->getMessage();
    }
    try {
        $pdo->exec("ALTER TABLE preasignaciones ADD CONSTRAINT fk_preasig_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id) ON DELETE CASCADE");
    } catch (Throwable $e) {
        $mensajes[] = 'Aviso: FK asignatura no añadida: ' . $e->getMessage();
    }
    $mensajes[] = 'OK: Tabla preasignaciones lista.';

    $mensajes[] = 'Migración completada correctamente.';
} catch (Throwable $e) {
    $error = true;
    $mensajes[] = 'ERROR: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Actualizar BD</title></head>
<body style="font-family:sans-serif;padding:2rem;">
    <h1>Actualizar base de datos</h1>
    <?php if ($error): ?>
        <div style="background:#fdd;border:1px solid #c00;padding:1rem;border-radius:4px;">
    <?php else: ?>
        <div style="background:#dfd;border:1px solid #0c0;padding:1rem;border-radius:4px;">
    <?php endif; ?>
        <?php foreach ($mensajes as $m): ?>
            <div><?= htmlspecialchars($m) ?></div>
        <?php endforeach; ?>
    </div>
    <p style="margin-top:1rem;"><a href="index.php?r=docentes/index">Volver a la aplicación</a></p>
</body>
</html>
