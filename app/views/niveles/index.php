<h1 class="page-title">Niveles / Cursos</h1>

<div class="nav-links">
    <a href="/GHE/public/index.php?r=etapas/index">Etapas</a>
    <a href="/GHE/public/index.php?r=niveles/sesiones">Sesiones por nivel</a>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-error">
        <?php foreach ($errores as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <h2>Añadir nivel / curso</h2>
    <?php if (empty($etapas)): ?>
        <p style="color:#e65100;font-weight:600;">No hay etapas. Crea primero una etapa educativa desde <a href="/GHE/public/index.php?r=etapas/index">Etapas</a>.</p>
    <?php else: ?>
    <form method="post" action="/GHE/public/index.php?r=niveles/store" class="validate">
        <label>Etapa</label>
        <select name="etapa_id" required>
            <option value="">Selecciona</option>
            <?php foreach ($etapas as $e): ?>
                <option value="<?= (int) $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Nombre (ej: 1º, 2º, Infantil 3 años…)</label>
        <input type="text" name="nombre" required>

        <label>Orden</label>
        <input type="number" name="orden" min="0" value="0" style="width:80px">

        <div class="form-actions">
            <button class="btn btn-success" type="submit">Guardar</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Niveles / Cursos</h2>
    <?php if (empty($niveles)): ?>
        <p>No hay niveles definidos. Crea uno desde el formulario de arriba.</p>
    <?php else: ?>
        <?php
        $etapaActual = null;
        foreach ($niveles as $n):
            if ($etapaActual !== $n['etapa']):
                $etapaActual = $n['etapa'];
                echo '<h3 style="color:#1a237e;margin:1.5rem 0 .5rem;">' . htmlspecialchars($etapaActual) . '</h3>';
            endif;
        ?>
            <div style="display:flex;align-items:center;gap:.5rem;padding:.3rem 0;">
                <span style="min-width:200px;"><?= htmlspecialchars($n['nombre']) ?> (orden <?= (int) $n['orden'] ?>)</span>
                <form method="post" action="/GHE/public/index.php?r=niveles/destroy&id=<?= (int) $n['id'] ?>" style="display:inline" data-confirm="¿Eliminar este nivel?">
                    <button class="btn btn-danger btn-small" type="submit">Borrar</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
