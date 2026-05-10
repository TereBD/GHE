<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Etapa.php';

final class EtapaController extends Controller
{
    public function index(): void
    {
        $this->view('etapas/index', [
            'items' => Etapa::all(),
            'titulo' => 'Etapas educativas',
        ]);
    }

    public function store(): void
    {
        $errores = [];
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $orden = (int) ($_POST['orden'] ?? 0);

        if ($nombre === '') $errores['nombre'] = 'El nombre es obligatorio.';

        if (!empty($errores)) {
            $this->view('etapas/index', [
                'items' => Etapa::all(),
                'titulo' => 'Etapas educativas',
                'errores' => $errores,
            ]);
            return;
        }

        try {
            Etapa::create($nombre, $orden);
        } catch (Throwable $e) {
            // Duplicado
        }

        $this->redirect('etapas/index');
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $orden = (int) ($_POST['orden'] ?? 0);

        if ($nombre !== '') {
            Etapa::update($id, $nombre, $orden);
        }

        $this->redirect('etapas/index');
    }

    public function destroy(): void
    {
        Etapa::delete((int) ($_GET['id'] ?? 0));
        $this->redirect('etapas/index');
    }
}
