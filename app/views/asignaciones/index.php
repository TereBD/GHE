<h1 class="page-title">Asignaciones docente-asignatura-grupo</h1>

<div class="card">
    <h2>Nueva asignación</h2>
    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <?php foreach ($errores as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/GHE/public/index.php?r=asignaciones/store" class="validate">
        <label>Docente</label>
        <select name="docente_id" required>
            <option value="">Selecciona</option>
            <?php foreach ($docentes as $docente): ?>
                <option value="<?= (int) $docente['id'] ?>">
                    <?= htmlspecialchars($docente['nombre'] . ' ' . $docente['apellido']) ?>
                </option>
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

        <label>Grupo</label>
        <select name="grupo_id" required>
            <option value="">Selecciona</option>
            <?php foreach ($grupos as $grupo): ?>
                <option value="<?= (int) $grupo['id'] ?>"><?= htmlspecialchars($grupo['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
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
            <th>Docente</th>
            <th>Asignatura</th>
            <th>Grupo</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= (int) $item['id'] ?></td>
                <td><?= htmlspecialchars($item['docente']) ?></td>
                <td><?= htmlspecialchars($item['asignatura']) ?></td>
                <td><?= htmlspecialchars($item['grupo']) ?></td>
                <td>
                    <form method="post" action="/GHE/public/index.php?r=asignaciones/destroy&id=<?= (int) $item['id'] ?>" style="display:inline" data-confirm="¿Eliminar esta asignación?">
                        <button class="btn btn-danger btn-small" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
