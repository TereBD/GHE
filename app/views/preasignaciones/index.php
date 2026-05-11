<h1 class="page-title">Pre-asignaciones</h1>

<p style="color:#555;margin-bottom:1rem;">
    Fija sesiones concretas que el generador debe respetar. El docente y el grupo quedarán ocupados en ese horario.
</p>

<div class="card">
    <h2>Nueva pre-asignación</h2>
    <form method="post" action="/GHE/public/index.php?r=preasignaciones/asignar">
        <label>Docente</label>
        <select name="docente_id" id="docente-select" required>
            <option value="">Selecciona</option>
            <?php foreach ($docentes as $d): ?>
                <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Grupo</label>
        <select name="grupo_id" id="grupo-select" required>
            <option value="">Selecciona</option>
            <?php foreach ($grupos as $g): ?>
                <option value="<?= (int) $g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Asignatura</label>
        <select name="asignatura_id" id="asignatura-select" required>
            <option value="">Primero selecciona docente y grupo</option>
        </select>

        <label>Día</label>
        <select name="dia" required>
            <option value="">Selecciona</option>
            <option value="Lunes">Lunes</option>
            <option value="Martes">Martes</option>
            <option value="Miércoles">Miércoles</option>
            <option value="Jueves">Jueves</option>
            <option value="Viernes">Viernes</option>
        </select>

        <label>Sesión</label>
        <select name="sesion" required>
            <option value="">Selecciona</option>
            <option value="1">1 (8:30-9:25)</option>
            <option value="2">2 (9:25-10:20)</option>
            <option value="3">3 (10:20-11:15)</option>
            <option value="5">5 (11:45-12:40)</option>
            <option value="6">6 (12:40-13:30)</option>
        </select>

        <div class="form-actions">
            <button class="btn btn-success" type="submit">Asignar sesión fija</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Pre-asignaciones actuales</h2>
    <?php if (empty($preasignaciones)): ?>
        <p>No hay pre-asignaciones. El generador decidirá la ubicación de todas las sesiones.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Docente</th>
                <th>Grupo</th>
                <th>Asignatura</th>
                <th>Día</th>
                <th>Sesión</th>
                <th>Acción</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($preasignaciones as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['docente']) ?></td>
                    <td><?= htmlspecialchars($p['grupo']) ?></td>
                    <td><?= htmlspecialchars($p['asignatura']) ?></td>
                    <td><?= htmlspecialchars($p['dia_semana']) ?></td>
                    <td><?= (int) $p['sesion'] ?></td>
                    <td>
                        <a href="/GHE/public/index.php?r=preasignaciones/eliminar&id=<?= (int) $p['id'] ?>"
                           class="btn btn-danger btn-small"
                           data-confirm="¿Eliminar esta pre-asignación?">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
var docenteSelect = document.getElementById('docente-select');
var grupoSelect = document.getElementById('grupo-select');
var asigSelect = document.getElementById('asignatura-select');

function cargarAsignaturas() {
    var docenteId = docenteSelect.value;
    var grupoId = grupoSelect.value;
    if (!docenteId || !grupoId) {
        asigSelect.innerHTML = '<option value="">Primero selecciona docente y grupo</option>';
        return;
    }
    fetch('/GHE/public/index.php?r=preasignaciones/asignaturas&docente_id=' + docenteId + '&grupo_id=' + grupoId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            asigSelect.innerHTML = '<option value="">Selecciona asignatura</option>';
            data.forEach(function(a) {
                var opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.nombre;
                asigSelect.appendChild(opt);
            });
        });
}

docenteSelect.addEventListener('change', cargarAsignaturas);
grupoSelect.addEventListener('change', cargarAsignaturas);
</script>
