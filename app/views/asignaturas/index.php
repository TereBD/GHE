<h1 class="page-title">Asignaturas</h1>

<div class="nav-links">
    <a href="/GHE/public/index.php?r=asignaturas/create" class="btn btn-primary">Nueva asignatura</a>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($asignaturas as $asignatura): ?>
            <tr>
                <td><?= (int) $asignatura['id'] ?></td>
                <td><?= htmlspecialchars($asignatura['nombre']) ?></td>
                <td>
                    <a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=asignaturas/edit&id=<?= (int) $asignatura['id'] ?>">Editar</a>
                    <form method="post" action="/GHE/public/index.php?r=asignaturas/destroy&id=<?= (int) $asignatura['id'] ?>" style="display:inline" data-confirm="¿Eliminar esta asignatura?">
                        <button class="btn btn-danger btn-small" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
