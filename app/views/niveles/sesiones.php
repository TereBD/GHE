<h1 class="page-title">Sesiones por nivel</h1>

<div class="nav-links">
    <a href="/GHE/public/index.php?r=niveles/index">Volver a niveles</a>
</div>

<p style="color:#555;margin-bottom:1rem;">Define cuántas sesiones semanales tiene cada asignatura en cada nivel. Pon 0 para eliminar.</p>

<?php if (empty($niveles) || empty($asignaturas)): ?>
    <div class="card">
        <p>Faltan niveles o asignaturas. Añádelos primero.</p>
    </div>
<?php else: ?>
    <div class="card" style="overflow-x:auto;">
        <form method="post" action="/GHE/public/index.php?r=niveles/guardarSesiones">
            <table style="font-size:.8rem;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="border:1px solid #ddd;padding:.4rem;background:#f5f5f5;text-align:left;min-width:140px;">Asignatura</th>
                        <?php foreach ($niveles as $n): ?>
                            <th style="border:1px solid #ddd;padding:.4rem;background:#f5f5f5;text-align:center;min-width:60px;">
                                <?= htmlspecialchars($n['nombre']) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($asignaturas as $a): ?>
                        <tr>
                            <td style="border:1px solid #ddd;padding:.3rem .4rem;font-weight:600;">
                                <?= htmlspecialchars($a['nombre']) ?>
                            </td>
                            <?php foreach ($niveles as $n): ?>
                                <?php $val = $map[$n['id'] . '-' . $a['id']] ?? 0; ?>
                                <td style="border:1px solid #ddd;padding:.3rem;text-align:center;">
                                    <input type="number" name="sesiones[<?= (int) $n['id'] ?>][<?= (int) $a['id'] ?>]"
                                           value="<?= $val ?>" min="0" max="30"
                                           style="width:50px;text-align:center;padding:.3rem;border:1px solid #ccc;border-radius:3px;">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="form-actions" style="margin-top:1rem;">
                <button class="btn btn-success" type="submit">Guardar todas las sesiones</button>
            </div>
        </form>
    </div>
<?php endif; ?>
