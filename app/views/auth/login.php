<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - GestHorarios Escolares</title>
    <link rel="stylesheet" href="/GHE/public/assets/css/style.css">
</head>
<body>
<div class="login-container">
    <div class="card">
        <h1>GestHorarios Escolares</h1>
        <p class="subtitle">Inicia sesión para acceder</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/GHE/public/index.php?r=auth/authenticate" class="validate">
            <label>Usuario</label>
            <input type="text" name="usuario" required autofocus>
            <div class="error-text"></div>

            <label>Contraseña</label>
            <input type="password" name="password" required>
            <div class="error-text"></div>

            <div class="form-actions">
                <button class="btn btn-success" type="submit" style="width:100%">Entrar</button>
            </div>
        </form>
    </div>
</div>
<script src="/GHE/public/assets/js/app.js"></script>
</body>
</html>
