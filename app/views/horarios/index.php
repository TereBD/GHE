<h1 class="page-title">Horarios</h1>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <form method="post" action="/GHE/public/index.php?r=horarios/generate" style="display:inline">
        <button class="btn btn-success" type="submit">Generar horario automático</button>
    </form>
</div>

<?php if (empty($horarioGrupos) && empty($horarioDocentes)): ?>
    <div class="card">
        <p>No hay horarios generados. Pulsa "Generar horario automático" para crear uno.</p>
    </div>
<?php else:

$diasOrden = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
$franjas = [
    1 => '8:30 a 9:25',
    2 => '9:25 a 10:20',
    3 => '10:20 a 11:15',
    4 => 'RECREO (11:15 a 11:45)',
    5 => '11:45 a 12:40',
    6 => '12:40 a 13:30',
];

function renderTablaHorario(array $dias, array $diasOrden, array $franjas): void
{
    ?>
    <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
        <thead>
        <tr>
            <th style="border:1px solid #ddd;padding:.5rem;background:#f5f5f5;">Sesión</th>
            <?php foreach ($diasOrden as $dia): ?>
                <th style="border:1px solid #ddd;padding:.5rem;background:#f5f5f5;"><?= $dia ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php for ($sesion = 1; $sesion <= 6; $sesion++): ?>
            <tr>
                <td style="border:1px solid #ddd;padding:.3rem .5rem;background:#f5f5f5;font-weight:600;white-space:nowrap;"><?= $franjas[$sesion] ?></td>
                <?php if ($sesion === 4): ?>
                    <?php foreach ($diasOrden as $dia): ?>
                        <td style="border:1px solid #ddd;padding:.3rem .5rem;text-align:center;font-weight:600;color:#e65100;background:#fff3e0;">RECREO</td>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($diasOrden as $dia): ?>
                        <td style="border:1px solid #ddd;padding:.3rem .5rem;">
                            <?php if (isset($dias[$dia][$sesion])): ?>
                                <div style="font-size:.8rem;line-height:1.3;">
                                    <div style="font-weight:600;color:#1a237e;"><?= htmlspecialchars($dias[$dia][$sesion]['asignatura']) ?></div>
                                    <div style="color:#666;"><?= htmlspecialchars($dias[$dia][$sesion]['docente'] ?? $dias[$dia][$sesion]['grupo'] ?? '') ?></div>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
    <?php
}
?>

    <div class="card" style="padding:0;">
        <div style="display:flex;border-bottom:2px solid #1a237e;">
            <button class="tab-btn active" data-tab="grupos" style="flex:1;padding:.8rem;border:none;background:#1a237e;color:#fff;cursor:pointer;font-weight:600;font-size:.95rem;" onclick="cambiarTab('grupos')">Horarios de Grupos</button>
            <button class="tab-btn" data-tab="docentes" style="flex:1;padding:.8rem;border:none;background:#e0e0e0;color:#333;cursor:pointer;font-weight:600;font-size:.95rem;" onclick="cambiarTab('docentes')">Horarios del Profesorado</button>
        </div>

        <div id="tab-grupos" class="tab-content" style="padding:1.5rem;">
            <?php if ($horarioGrupos): ?>
                <div style="margin-bottom:1rem;">
                    <label style="font-weight:600;margin-right:.5rem;">Descargar PDF:</label>
                    <select id="select-grupo" style="padding:.4rem .6rem;border:1px solid #ccc;border-radius:4px;">
                        <option value="">Selecciona un grupo</option>
                        <?php foreach ($grupos as $g): ?>
                            <option value="<?= (int) $g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary btn-small" onclick="descargarPdfGrupo()">Descargar PDF</button>
                </div>

                <?php foreach ($horarioGrupos as $grupo => $dias): ?>
                    <div class="horario-grid" style="margin-bottom:1.5rem;">
                        <h3 style="margin-bottom:.5rem;color:#1a237e;">Grupo: <?= htmlspecialchars($grupo) ?></h3>
                        <?php renderTablaHorario($dias, $diasOrden, $franjas); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay horarios de grupos generados.</p>
            <?php endif; ?>
        </div>

        <div id="tab-docentes" class="tab-content" style="padding:1.5rem;display:none;">
            <?php if ($horarioDocentes): ?>
                <div style="margin-bottom:1rem;">
                    <label style="font-weight:600;margin-right:.5rem;">Descargar PDF:</label>
                    <select id="select-docente" style="padding:.4rem .6rem;border:1px solid #ccc;border-radius:4px;">
                        <option value="">Selecciona un profesor</option>
                        <?php foreach ($docentes as $d): ?>
                            <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['nombre'] . ' ' . $d['apellido']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary btn-small" onclick="descargarPdfDocente()">Descargar PDF</button>
                </div>

                <?php foreach ($horarioDocentes as $docente => $dias): ?>
                    <div class="horario-grid" style="margin-bottom:1.5rem;">
                        <h3 style="margin-bottom:.5rem;color:#1a237e;">Profesor: <?= htmlspecialchars($docente) ?></h3>
                        <?php renderTablaHorario($dias, $diasOrden, $franjas); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay horarios de profesores generados.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function cambiarTab(tab) {
        document.querySelectorAll('.tab-content').forEach(function(el) { el.style.display = 'none'; });
        document.querySelectorAll('.tab-btn').forEach(function(el) {
            el.style.background = '#e0e0e0';
            el.style.color = '#333';
        });
        document.getElementById('tab-' + tab).style.display = 'block';
        var btn = document.querySelector('.tab-btn[data-tab="' + tab + '"]');
        btn.style.background = '#1a237e';
        btn.style.color = '#fff';
    }

    function descargarPdfGrupo() {
        var id = document.getElementById('select-grupo').value;
        if (!id) { alert('Selecciona un grupo.'); return; }
        window.location.href = '/GHE/public/index.php?r=horarios/pdf&tipo=grupo&id=' + id;
    }

    function descargarPdfDocente() {
        var id = document.getElementById('select-docente').value;
        if (!id) { alert('Selecciona un profesor.'); return; }
        window.location.href = '/GHE/public/index.php?r=horarios/pdf&tipo=docente&id=' + id;
    }
    </script>
<?php endif; ?>
