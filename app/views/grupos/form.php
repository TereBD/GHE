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
        <label>Nivel / Curso</label>
        <select name="nivel_id" required>
            <option value="">Selecciona</option>
            <?php
            $etapaActual = null;
            foreach ($niveles as $n):
                if ($etapaActual !== $n['etapa']):
                    $etapaActual = $n['etapa'];
                    echo '<optgroup label="' . htmlspecialchars($etapaActual) . '">';
                endif;
            ?>
                <option value="<?= (int) $n['id'] ?>" <?= ($grupo['nivel_id'] ?? '') === (string) $n['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($n['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="error-text"><?= htmlspecialchars($errores['nivel_id'] ?? '') ?></div>

        <label>Letra del grupo</label>
        <select name="letra" required>
            <option value="">Selecciona</option>
            <?php foreach (['A', 'B', 'C', 'D'] as $l): ?>
                <option value="<?= $l ?>" <?= ($grupo['letra'] ?? '') === $l ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <div class="error-text"><?= htmlspecialchars($errores['letra'] ?? '') ?></div>

        <label>Nombre del grupo</label>
        <input type="text" name="nombre" required
               value="<?= htmlspecialchars($grupo['nombre'] ?? '') ?>"
               placeholder="Se genera automáticamente (ej: 1º A)">
        <div class="error-text"><?= htmlspecialchars($errores['nombre'] ?? '') ?></div>

        <label>Tutor / Tutora</label>
        <select name="tutor_id">
            <option value="">Ninguno</option>
            <?php foreach ($docentes as $d): ?>
                <option value="<?= (int) $d['id'] ?>" <?= ($grupo['tutor_id'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="form-actions">
            <button class="btn btn-success" type="submit">Guardar</button>
            <a class="btn btn-primary" href="/GHE/public/index.php?r=grupos/index">Cancelar</a>
        </div>
    </form>
</div>
