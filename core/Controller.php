<?php

declare(strict_types=1);

abstract class Controller
{
    protected function view(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        require __DIR__ . '/../app/views/layout/header.php';
        require __DIR__ . '/../app/views/' . $path . '.php';
        require __DIR__ . '/../app/views/layout/footer.php';
    }

    protected function viewSimple(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../app/views/' . $path . '.php';
    }

    protected function redirect(string $route): void
    {
        header('Location: /GHE/public/index.php?r=' . urlencode($route));
        exit;
    }
}
