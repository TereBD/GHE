<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/HorarioGenerator.php';
require_once __DIR__ . '/../models/Docente.php';
require_once __DIR__ . '/../models/Grupo.php';

final class HorarioController extends Controller
{
    public function index(): void
    {
        $horarioGrupos = HorarioGenerator::cargarActual();
        $horarioDocentes = HorarioGenerator::cargarPorDocente();

        $this->view('horarios/index', [
            'horarioGrupos' => $horarioGrupos,
            'horarioDocentes' => $horarioDocentes,
            'docentes' => Docente::all(),
            'grupos' => Grupo::reales(),
            'error' => null,
            'titulo' => 'Horarios',
        ]);
    }

    public function generate(): void
    {
        try {
            HorarioGenerator::generar();
            $horarioGrupos = HorarioGenerator::cargarActual();
            $horarioDocentes = HorarioGenerator::cargarPorDocente();

            $this->view('horarios/index', [
                'horarioGrupos' => $horarioGrupos,
                'horarioDocentes' => $horarioDocentes,
                'docentes' => Docente::all(),
                'grupos' => Grupo::reales(),
                'error' => null,
                'titulo' => 'Horario generado',
            ]);
        } catch (Throwable $e) {
            $this->view('horarios/index', [
                'horarioGrupos' => [],
                'horarioDocentes' => [],
                'docentes' => Docente::all(),
                'grupos' => Grupo::reales(),
                'error' => $e->getMessage(),
                'titulo' => 'Horarios',
            ]);
        }
    }

    public function pdf(): void
    {
        $tipo = $_GET['tipo'] ?? 'grupo';
        $id = (int) ($_GET['id'] ?? 0);

        require_once __DIR__ . '/../../lib/Pdf.php';

        if ($tipo === 'docente') {
            $this->generarPdfDocente($id);
        } else {
            $this->generarPdfGrupo($id);
        }
    }

    private function generarPdfGrupo(int $grupoId): void
    {
        $pdo = Database::connection();

        $grupo = $pdo->prepare('SELECT * FROM grupos WHERE id = :id');
        $grupo->execute(['id' => $grupoId]);
        $infoGrupo = $grupo->fetch();

        if (!$infoGrupo) {
            http_response_code(404);
            echo 'Grupo no encontrado';
            exit;
        }

        $rows = $pdo->prepare(
            'SELECT h.dia_semana, h.sesion, a.nombre AS asignatura,
                    CONCAT(d.nombre, " ", d.apellido) AS docente
             FROM horarios h
             JOIN asignaturas a ON a.id = h.asignatura_id
             JOIN docentes d ON d.id = h.docente_id
             WHERE h.grupo_id = :grupo_id
             ORDER BY FIELD(h.dia_semana, "Lunes", "Martes", "Miércoles", "Jueves", "Viernes"), h.sesion'
        );
        $rows->execute(['grupo_id' => $grupoId]);
        $data = $rows->fetchAll();

        $pdf = new Pdf('L', 'mm', 'A4');
        $pdf->setTitulo('Horario - Grupo ' . $infoGrupo['nombre']);
        $pdf->generar($data, $infoGrupo['nombre']);
        $pdf->Output('D', 'horario_grupo_' . $infoGrupo['nombre'] . '.pdf');
        exit;
    }

    private function generarPdfDocente(int $docenteId): void
    {
        $pdo = Database::connection();

        $docente = $pdo->prepare('SELECT * FROM docentes WHERE id = :id');
        $docente->execute(['id' => $docenteId]);
        $infoDocente = $docente->fetch();

        if (!$infoDocente) {
            http_response_code(404);
            echo 'Docente no encontrado';
            exit;
        }

        $nombreDocente = $infoDocente['nombre'] . ' ' . $infoDocente['apellido'];

        $rows = $pdo->prepare(
            'SELECT h.dia_semana, h.sesion, a.nombre AS asignatura, g.nombre AS grupo
             FROM horarios h
             JOIN asignaturas a ON a.id = h.asignatura_id
             JOIN grupos g ON g.id = h.grupo_id
             WHERE h.docente_id = :docente_id
             ORDER BY FIELD(h.dia_semana, "Lunes", "Martes", "Miércoles", "Jueves", "Viernes"), h.sesion'
        );
        $rows->execute(['docente_id' => $docenteId]);
        $data = $rows->fetchAll();

        $pdf = new Pdf('L', 'mm', 'A4');
        $pdf->setTitulo('Horario - ' . $nombreDocente);
        $pdf->generar($data, $nombreDocente);
        $pdf->Output('D', 'horario_' . str_replace(' ', '_', $nombreDocente) . '.pdf');
        exit;
    }
}
