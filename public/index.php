<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../app/controllers/DocenteController.php';
require_once __DIR__ . '/../app/controllers/HorarioController.php';
require_once __DIR__ . '/../app/controllers/AsignaturaController.php';
require_once __DIR__ . '/../app/controllers/GrupoController.php';
require_once __DIR__ . '/../app/controllers/AsignacionController.php';
require_once __DIR__ . '/../app/controllers/NivelController.php';
require_once __DIR__ . '/../app/controllers/EtapaController.php';
require_once __DIR__ . '/../app/controllers/PreasignacionController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$route = $_GET['r'] ?? 'auth/login';
[$controllerName, $action] = array_pad(explode('/', $route), 2, 'index');

$rutasPublicas = ['auth'];

if (!in_array($controllerName, $rutasPublicas, true) && !isset($_SESSION['usuario'])) {
    header('Location: /GHE/public/index.php?r=auth/login');
    exit;
}

$routes = [
    'home' => static function (): void {
        $controller = new class extends Controller {
            public function home(): void
            {
                $this->view('home/index', ['titulo' => 'GestHorarios Escolares']);
            }
        };
        $controller->home();
    },
    'docentes' => new DocenteController(),
    'asignaturas' => new AsignaturaController(),
    'grupos' => new GrupoController(),
    'asignaciones' => new AsignacionController(),
    'niveles' => new NivelController(),
    'etapas' => new EtapaController(),
    'preasignaciones' => new PreasignacionController(),
    'horarios' => new HorarioController(),
    'auth' => new AuthController(),
];

if (!array_key_exists($controllerName, $routes)) {
    http_response_code(404);
    echo 'Ruta no encontrada';
    exit;
}

$controller = $routes[$controllerName];
if (is_callable($controller)) {
    $controller();
    exit;
}

if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo 'Accion no encontrada';
    exit;
}

$controller->{$action}();
