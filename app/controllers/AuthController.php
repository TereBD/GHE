<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Usuario.php';

final class AuthController extends Controller
{
    public function login(): void
    {
        if (isset($_SESSION['usuario'])) {
            $this->redirect('home/index');
        }
        $this->viewSimple('auth/login', ['titulo' => 'Iniciar sesión']);
    }

    public function authenticate(): void
    {
        $usuario = trim((string) ($_POST['usuario'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($usuario === '' || $password === '') {
            $this->viewSimple('auth/login', [
                'error' => 'Usuario y contraseña son obligatorios.',
                'titulo' => 'Iniciar sesión',
            ]);
            return;
        }

        $user = Usuario::autenticar($usuario, $password);

        if ($user === null) {
            $this->viewSimple('auth/login', [
                'error' => 'Usuario o contraseña incorrectos.',
                'titulo' => 'Iniciar sesión',
            ]);
            return;
        }

        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['usuario_id'] = (int) $user['id'];
        $this->redirect('home/index');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('auth/login');
    }
}
