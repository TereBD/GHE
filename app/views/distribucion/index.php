<h1 class="page-title">Distribución horaria por grupo</h1>

<div class="card">
    <h2>Nueva distribución</h2>
    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <?php foreach ($errores as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/GHE/public/index.php?r=distribucion/store" class="validate">
        <label>Grupo</label>
        <select name="grupo_id" required>
            <option value="">Selecciona</option>
            <?php foreach ($grupos as $grupo): ?>
                <option value="<?= (int) $grupo['id'] ?>"><?= htmlspecialchars($grupo['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="error-text"></div>

        <label>Asignatura</label>
        <select name="asignatura_id" required>
            <option value="">Selecciona</option>
            <?php foreach ($asignaturas as $asignatura): ?>
                <option value="<?= (int) $asignatura['id'] ?>"><?= htmlspecialchars($asignatura['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="error-text"></div>

        <label>Sesiones / semana</label>
        <input type="number" name="sesiones_semana" min="1" max="30" required>
        <div class="error-text"></div>

        <div class="form-actions">
            <button class="btn btn-success" type="submit">Guardar</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Registros</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Grupo</th>
            <th>Asignatura</th>
            <th>Sesiones/semana</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= (int) $item['id'] ?></td>
                <td><?= htmlspecialchars($item['grupo']) ?></td>
                <td><?= htmlspecialchars($item['asignatura']) ?></td>
                <td><?= (int) $item['sesiones_semana'] ?></td>
                <td>
                    <form method="post" action="/GHE/public/index.php?r=distribucion/destroy&id=<?= (int) $item['id'] ?>" style="display:inline" data-confirm="¿Eliminar esta distribución?">
                        <button class="btn btn-danger btn-small" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
