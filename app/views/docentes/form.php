<h1 class="page-title"><?= $docente ? 'Editar docente' : 'Nuevo docente' ?></h1>

<div class="nav-links">
    <a href="/GHE/public/index.php?r=docentes/index">Volver</a>
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
        <input type="text" name="nombre" required value="<?= htmlspecialchars($docente['nombre'] ?? '') ?>">
        <div class="error-text"><?= htmlspecialchars($errores['nombre'] ?? '') ?></div>

        <label>Apellido</label>
        <input type="text" name="apellido" required value="<?= htmlspecialchars($docente['apellido'] ?? '') ?>">
        <div class="error-text"><?= htmlspecialchars($errores['apellido'] ?? '') ?></div>

        <label>Tipo</label>
        <select name="tipo">
            <option value="tutor" <?= ($docente['tipo'] ?? 'tutor') === 'tutor' ? 'selected' : '' ?>>Tutor (asigna grupo + asignaturas)</option>
            <option value="especialista" <?= ($docente['tipo'] ?? '') === 'especialista' ? 'selected' : '' ?>>Especialista (asigna asignatura + grupos)</option>
        </select>
        <div class="error-text"><?= htmlspecialchars($errores['tipo'] ?? '') ?></div>

        <label>Horas máximas semanales</label>
        <input type="number" name="horas_maximas" min="1" required value="<?= (int) ($docente['horas_maximas'] ?? 18) ?>">
        <div class="error-text"><?= htmlspecialchars($errores['horas_maximas'] ?? '') ?></div>

        <label>Horas PAT</label>
        <input type="number" name="horas_pat" min="0" required value="<?= (int) ($docente['horas_pat'] ?? 0) ?>">
        <div class="error-text"><?= htmlspecialchars($errores['horas_pat'] ?? '') ?></div>

        <label>Horas Proyecto</label>
        <input type="number" name="horas_proyecto" min="0" required value="<?= (int) ($docente['horas_proyecto'] ?? 0) ?>">
        <div class="error-text"><?= htmlspecialchars($errores['horas_proyecto'] ?? '') ?></div>

        <div class="form-actions">
            <button class="btn btn-success" type="submit">Guardar</button>
            <a class="btn btn-primary" href="/GHE/public/index.php?r=docentes/index">Cancelar</a>
        </div>
    </form>
</div>
