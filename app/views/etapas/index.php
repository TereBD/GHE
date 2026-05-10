<h1 class="page-title">Etapas educativas</h1>

<div class="nav-links">
    <a href="/GHE/public/index.php?r=niveles/index">Niveles / Cursos</a>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-error">
        <?php foreach ($errores as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <h2>Añadir etapa</h2>
    <form method="post" action="/GHE/public/index.php?r=etapas/store" class="validate">
        <label>Nombre (ej: Infantil, Primaria, ESO)</label>
        <input type="text" name="nombre" required>
        <label>Orden</label>
        <input type="number" name="orden" min="0" value="0" style="width:80px">
        <div class="form-actions">
            <button class="btn btn-success" type="submit">Guardar</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Etapas</h2>
    <?php if (empty($items)): ?>
        <p>No hay etapas. Crea al menos una para poder definir niveles.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Nombre</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $e): ?>
                    <tr>
                        <td><?= (int) $e['orden'] ?></td>
                        <td><?= htmlspecialchars($e['nombre']) ?></td>
                        <td>
                            <form method="post" action="/GHE/public/index.php?r=etapas/destroy&id=<?= (int) $e['id'] ?>" style="display:inline" data-confirm="¿Eliminar esta etapa?">
                                <button class="btn btn-danger btn-small" type="submit">Borrar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
