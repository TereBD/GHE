<h1 class="page-title"><?= $grupo ? 'Editar grupo' : 'Nuevo grupo' ?></h1>

<div class="nav-links">
    <a href="/GHE/public/index.php?r=grupos/index">Volver</a>
</div>

<div class="card">
    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <?php foreach ($errores as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/GHE/public/index.php?r=<?= htmlspecialchars($action) ?>" class="validate">
        <label>Nombre</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($grupo['nombre'] ?? '') ?>">
        <div class="error-text"><?= htmlspecialchars($errores['nombre'] ?? '') ?></div>

        <div class="form-actions">
            <button class="btn btn-success" type="submit">Guardar</button>
            <a class="btn btn-primary" href="/GHE/public/index.php?r=grupos/index">Cancelar</a>
        </div>
    </form>
</div>
