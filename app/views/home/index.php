<h1 class="page-title">GestHorarios Escolares</h1>

<div class="card">
    <p style="margin-bottom:1rem;color:#555;">Bienvenido, <?= htmlspecialchars($_SESSION['usuario'] ?? '') ?>. Selecciona una opción:</p>

    <table>
        <thead>
        <tr>
            <th>Módulo</th>
            <th>Descripción</th>
            <th>Acción</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Docentes</td>
            <td>Gestionar el profesorado (alta, edición, eliminación)</td>
            <td><a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=docentes/index">Acceder</a></td>
        </tr>
        <tr>
            <td>Asignaturas</td>
            <td>Gestionar las asignaturas del centro</td>
            <td><a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=asignaturas/index">Acceder</a></td>
        </tr>
        <tr>
            <td>Grupos</td>
            <td>Gestionar los grupos de alumnos</td>
            <td><a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=grupos/index">Acceder</a></td>
        </tr>
        <tr>
            <td>Asignaciones</td>
            <td>Asignar docentes a asignaturas y grupos</td>
            <td><a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=asignaciones/index">Acceder</a></td>
        </tr>
        <tr>
            <td>Niveles y sesiones</td>
            <td>Gestionar etapas, niveles/cursos y definir sesiones semanales por asignatura (vista matriz)</td>
            <td><a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=niveles/index">Acceder</a></td>
        </tr>
        <tr>
            <td>Horarios</td>
            <td>Generar horarios automáticos</td>
            <td><a class="btn btn-primary btn-small" href="/GHE/public/index.php?r=horarios/index">Acceder</a></td>
        </tr>
        </tbody>
    </table>
</div>
