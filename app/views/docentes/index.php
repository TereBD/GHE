<h1 class="page-title">Docentes</h1>

<div class="nav-links">
    <a href="/GHE/public/index.php?r=docentes/create" class="btn btn-primary">Nuevo docente</a>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Horas máximas</th>
            <th>Horas PAT/Proyecto</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($docentes as $docente): ?>
            <tr>
                <td><?= (int) $docente['id'] ?></td>
                <td><?= htmlspecialchars($docente['nombre']) ?></td>
                <td><?= htmlspecialchars($docente['apellido']) ?></td>
                <td><?= (int) $docente['horas_maximas'] ?></td>
                <td><?= (int) $docente['horas_pat_proyecto'] ?></td>
                <td>
                    <a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=docentes/edit&id=<?= (int) $docente['id'] ?>">Editar</a>
                    <form method="post" action="/GHE/public/index.php?r=docentes/destroy&id=<?= (int) $docente['id'] ?>" style="display:inline" data-confirm="¿Eliminar este docente?">
                        <button class="btn btn-danger btn-small" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
