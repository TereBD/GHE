<?php
require_once __DIR__ . '/../core/Database.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['confirm'] === '1') {
    try {
        $pdo = Database::connection();
        $hash = password_hash('password', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (usuario, password) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE password = VALUES(password)"
        );
        $stmt->execute(['admin', $hash]);
        $mensaje = 'Contraseña restablecida. Usuario: admin / Contraseña: password';
    } catch (Throwable $e) {
        $mensaje = 'Error: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resetear admin</title>
</head>
<body style="font-family:sans-serif;padding:2rem;max-width:500px;margin:auto;">
    <h1>Restablecer usuario admin</h1>
    <?php if ($mensaje): ?>
        <p style="background:#d4edda;padding:1rem;border-radius:4px;"><?= htmlspecialchars($mensaje) ?></p>
        <p><a href="index.php?r=auth/login">Ir al login</a></p>
    <?php else: ?>
        <p>Esto restablecerá el usuario <strong>admin</strong> con contraseña <strong>password</strong>.</p>
        <form method="post">
            <input type="hidden" name="confirm" value="1">
            <button type="submit" style="padding:.5rem 1rem;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;">Restablecer</button>
        </form>
    <?php endif; ?>
</body>
</html>
