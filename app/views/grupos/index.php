<h1 class="page-title">Grupos</h1>

<div class="nav-links">
    <a href="/GHE/public/index.php?r=grupos/create" class="btn btn-primary">Nuevo grupo</a>
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
        <?php foreach ($grupos as $grupo): ?>
            <tr>
                <td><?= (int) $grupo['id'] ?></td>
                <td><?= htmlspecialchars($grupo['nombre']) ?></td>
                <td>
                    <a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=grupos/edit&id=<?= (int) $grupo['id'] ?>">Editar</a>
                    <form method="post" action="/GHE/public/index.php?r=grupos/destroy&id=<?= (int) $grupo['id'] ?>" style="display:inline" data-confirm="¿Eliminar este grupo?">
                        <button class="btn btn-danger btn-small" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
