<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'GestHorarios Escolares' ?></title>
    <link rel="stylesheet" href="/GHE/public/assets/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <a href="/GHE/public/index.php"><h1>GestHorarios Escolares</h1></a>
        <nav>
            <a href="/GHE/public/index.php?r=docentes/index">Docentes</a>
            <a href="/GHE/public/index.php?r=asignaturas/index">Asignaturas</a>
            <a href="/GHE/public/index.php?r=grupos/index">Grupos</a>
            <a href="/GHE/public/index.php?r=asignaciones/index">Asignaciones</a>
            <a href="/GHE/public/index.php?r=preasignaciones/index">Pre-asignaciones</a>
            <a href="/GHE/public/index.php?r=etapas/index">Etapas</a>
            <a href="/GHE/public/index.php?r=niveles/index">Niveles</a>
            <a href="/GHE/public/index.php?r=niveles/sesiones">Sesiones</a>
            <a href="/GHE/public/index.php?r=horarios/index">Horarios</a>
            <?php if (isset($_SESSION['usuario'])): ?>
                <a href="/GHE/public/index.php?r=auth/logout">Salir (<?= htmlspecialchars($_SESSION['usuario']) ?>)</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
