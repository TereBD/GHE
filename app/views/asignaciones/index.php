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
        <select name="docente_id" id="docente-select" required>
            <option value="">Selecciona</option>
            <?php foreach ($docentes as $docente): ?>
                <option value="<?= (int) $docente['id'] ?>" data-tipo="<?= ($docente['tipo'] ?? 'tutor') ?>">
                    <?= htmlspecialchars($docente['nombre'] . ' ' . $docente['apellido']) ?>
                    (<?= ($docente['tipo'] ?? 'tutor') === 'especialista' ? 'Especialista' : 'Tutor' ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <div class="error-text"></div>

        <!-- MODO TUTOR -->
        <div id="modo-tutor">
            <label>Grupo</label>
            <select name="grupo_id" id="grupo-select">
                <option value="">Selecciona</option>
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?= (int) $grupo['id'] ?>"><?= htmlspecialchars($grupo['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="error-text"></div>

            <label>Asignaturas (marca las que correspondan)</label>
            <div style="margin-bottom:1rem;">
                <?php foreach ($asignaturas as $asignatura): ?>
                    <div style="margin-bottom:6px;">
                        <label style="font-weight:400;font-size:.9rem;cursor:pointer;">
                            <input type="checkbox" name="asignaturas_ids[]" value="<?= (int) $asignatura['id'] ?>" class="asig-cb">
                            <?= htmlspecialchars($asignatura['nombre']) ?>
                        </label>
                        <label class="tutoria-check" style="font-weight:400;font-size:.8rem;color:#e65100;display:none;margin-left:1.5rem;cursor:pointer;">
                            <input type="checkbox" name="tutorias_ids[]" value="<?= (int) $asignatura['id'] ?>">
                            Es tutoría (horario flexible)
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="error-text"><?= htmlspecialchars($errores['asignaturas_ids'] ?? '') ?></div>
        </div>

        <!-- MODO ESPECIALISTA -->
        <div id="modo-especialista" style="display:none;">
            <label>Asignatura</label>
            <select name="asignatura_id" id="asignatura-select">
                <option value="">Selecciona</option>
                <?php foreach ($asignaturas as $asignatura): ?>
                    <option value="<?= (int) $asignatura['id'] ?>"><?= htmlspecialchars($asignatura['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="error-text"></div>

            <label>Grupos (marca los que correspondan)</label>
            <div style="margin-bottom:1rem;">
                <?php foreach ($grupos as $grupo): ?>
                    <div style="margin-bottom:6px;">
                        <label style="font-weight:400;font-size:.9rem;cursor:pointer;">
                            <input type="checkbox" name="grupos_ids[]" value="<?= (int) $grupo['id'] ?>">
                            <?= htmlspecialchars($grupo['nombre']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="error-text"><?= htmlspecialchars($errores['grupos_ids'] ?? '') ?></div>
        </div>

        <input type="hidden" name="modo" id="modo-input" value="tutor">

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
            <th>Docente</th>
            <th>Asignaturas y grupos</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $grupoDocente): ?>
            <tr>
                <td style="vertical-align:top;font-weight:600;padding-top:.8rem;"><?= htmlspecialchars($grupoDocente['docente']) ?></td>
                <td>
                    <?php foreach ($grupoDocente['asignaciones'] as $asig): ?>
                        <div style="margin-bottom:4px;padding:4px 0;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                            <span>
                                <?= htmlspecialchars($asig['asignatura']) ?> —
                                <em><?= htmlspecialchars($asig['grupo']) ?></em>
                                <?php if ($asig['es_tutoria']): ?>
                                    <span style="background:#fff3e0;color:#e65100;font-size:.75rem;padding:.1rem .4rem;border-radius:3px;margin-left:.5rem;">Tutoría</span>
                                <?php endif; ?>
                            </span>
                            <form method="post" action="/GHE/public/index.php?r=asignaciones/destroy&id=<?= (int) $asig['id'] ?>" style="display:inline" data-confirm="¿Eliminar esta asignación?">
                                <button class="btn btn-danger btn-small" type="submit" style="padding:.1rem .4rem;font-size:.7rem;">X</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Cambiar modo según el tipo de docente seleccionado
var docenteSelect = document.getElementById('docente-select');
var modoTutor = document.getElementById('modo-tutor');
var modoEspecialista = document.getElementById('modo-especialista');
var modoInput = document.getElementById('modo-input');

function actualizarModo() {
    var opt = docenteSelect.options[docenteSelect.selectedIndex];
    var tipo = opt ? opt.getAttribute('data-tipo') : 'tutor';
    if (tipo === 'especialista') {
        modoTutor.style.display = 'none';
        modoEspecialista.style.display = '';
        modoInput.value = 'especialista';
        // Remove required from tutor fields, add to specialist fields
        document.getElementById('grupo-select').removeAttribute('required');
        document.getElementById('asignatura-select').setAttribute('required', '');
    } else {
        modoTutor.style.display = '';
        modoEspecialista.style.display = 'none';
        modoInput.value = 'tutor';
        document.getElementById('grupo-select').setAttribute('required', '');
        document.getElementById('asignatura-select').removeAttribute('required');
    }
}

docenteSelect.addEventListener('change', actualizarModo);
actualizarModo();

// Mostrar check de tutoría al marcar asignatura
document.querySelectorAll('.asig-cb').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var tut = this.parentElement.parentElement.querySelector('.tutoria-check');
        if (tut) {
            tut.style.display = this.checked ? '' : 'none';
        }
    });
});
</script>
