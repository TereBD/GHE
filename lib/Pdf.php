<?php

declare(strict_types=1);

final class Pdf
{
    private string $titulo = '';
    private string $contenido = '';
    private float $y;
    private int $pagina = 0;
    private string $orientacion;
    private float $anchoPagina;
    private float $altoPagina;
    private float $margen = 15;

    private const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

    public function __construct(string $orientacion = 'L', string $unidad = 'mm', string $formato = 'A4')
    {
        $this->orientacion = $orientacion;
        if ($formato === 'A4') {
            if ($orientacion === 'L') {
                $this->anchoPagina = 297;
                $this->altoPagina = 210;
            } else {
                $this->anchoPagina = 210;
                $this->altoPagina = 297;
            }
        }
    }

    public function setTitulo(string $titulo): void
    {
        $this->titulo = $titulo;
    }

    public function generar(array $data, string $nombreEntidad, string $modo = 'grupo'): void
    {
        $this->nuevaPagina();
        $this->escribirCabecera($nombreEntidad);

        $agrupado = [];
        foreach ($data as $fila) {
            $agrupado[$fila['dia_semana']][(int) $fila['sesion']] = $fila;
        }

        $xInicio = $this->margen;
        $anchoTotal = $this->anchoPagina - 2 * $this->margen;
        $anchoPrimera = 45;
        $anchoDia = ($anchoTotal - $anchoPrimera) / 5;
        $altoFila = 22;

        $this->escribirCelda($xInicio, $this->y, $anchoPrimera, 10, 'Sesión', 1, 1);
        $x = $xInicio + $anchoPrimera;
        foreach (self::DIAS as $dia) {
            $this->escribirCelda($x, $this->y, $anchoDia, 10, $dia, 1, 1);
            $x += $anchoDia;
        }
        $this->y += 10;

        $franjas = [
            1 => '8:30-9:25',
            2 => '9:25-10:20',
            3 => '10:20-11:15',
            4 => 'RECREO',
            5 => '11:45-12:40',
            6 => '12:40-13:30',
        ];

        for ($sesion = 1; $sesion <= 6; $sesion++) {
            if ($this->y + $altoFila > $this->altoPagina - $this->margen) {
                $this->nuevaPagina();
                $this->escribirCabecera($nombreEntidad);

                $this->escribirCelda($xInicio, $this->y, $anchoPrimera, 10, 'Sesión', 1, 1);
                $x = $xInicio + $anchoPrimera;
                foreach (self::DIAS as $dia) {
                    $this->escribirCelda($x, $this->y, $anchoDia, 10, $dia, 1, 1);
                    $x += $anchoDia;
                }
                $this->y += 10;
            }

            $this->escribirCelda($xInicio, $this->y, $anchoPrimera, $altoFila, $franjas[$sesion], 1, 1);

            $x = $xInicio + $anchoPrimera;
            foreach (self::DIAS as $dia) {
                if ($sesion === 4) {
                    $this->escribirCelda($x, $this->y, $anchoDia, $altoFila, 'RECREO', 1, 1);
                } elseif (isset($agrupado[$dia][$sesion])) {
                    if ($modo === 'docente') {
                        $txt = ($agrupado[$dia][$sesion]['grupo'] ?? '') . "\n" . $agrupado[$dia][$sesion]['asignatura'];
                    } else {
                        $txt = $agrupado[$dia][$sesion]['asignatura'] . "\n" . ($agrupado[$dia][$sesion]['docente'] ?? '');
                    }
                    $this->escribirCelda($x, $this->y, $anchoDia, $altoFila, $txt, 1, 0);
                } else {
                    $this->escribirCelda($x, $this->y, $anchoDia, $altoFila, '', 1, 0);
                }
                $x += $anchoDia;
            }
            $this->y += $altoFila;
        }
    }

    private function escribirCabecera(string $nombre): void
    {
        $this->escribirTexto($this->margen, $this->y, $this->titulo, 12);
        $this->y += 8;
    }

    private function nuevaPagina(): void
    {
        $this->pagina++;
        $this->y = $this->margen;
    }

    private function escribirCelda(float $x, float $y, float $w, float $h, string $txt, int $borde = 1, int $align = 0): void
    {
        $this->contenido .= sprintf(
            "%.2f %.2f %.2f %.2f re S\n",
            $x, $this->altoPagina - $y - $h, $w, $h
        );

        $lines = explode("\n", $txt);
        $lineHeight = $h / max(count($lines), 1);
        $lineY = $y + ($h - count($lines) * $lineHeight) / 2;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $this->contenido .= sprintf(
                    "BT /F1 %.2f Tf ET BT %.2f %.2f Td (%s) Tj ET\n",
                    min($lineHeight * 0.55, 8),
                    $x + 1.5,
                    $this->altoPagina - $lineY - $lineHeight * 0.75,
                    $this->escaparTexto($line)
                );
            }
            $lineY += $lineHeight;
        }
    }

    private function escribirTexto(float $x, float $y, string $txt, float $tamano): void
    {
        $this->contenido .= sprintf(
            "BT /F1 %.2f Tf ET BT %.2f %.2f Td (%s) Tj ET\n",
            $tamano,
            $x,
            $this->altoPagina - $y - $tamano * 0.35,
            $this->escaparTexto($txt)
        );
    }

    private function escaparTexto(string $txt): string
    {
        $txt = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $txt);
        $txt = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt);
        return $txt;
    }

    public function Output(string $dest = 'D', string $name = 'document.pdf'): void
    {
        $pdf = $this->buildPdf();

        if ($dest === 'D') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Length: ' . strlen($pdf));
            echo $pdf;
        } elseif ($dest === 'I') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $name . '"');
            echo $pdf;
        }
    }

    private function buildPdf(): string
    {
        $objects = [];

        // Object 1: Catalog
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj";

        // Object 2: Pages
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj";

        // Object 3: Page
        $pageStream = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->anchoPagina} {$this->altoPagina}] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj";

        // Object 4: Content stream
        $stream = $this->contenido;
        $streamObj = "4 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream\nendobj";

        // Object 5: Font
        $fontObj = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj";

        // XRef
        $offset = 0;
        $offsets = [];
        $body = '';
        foreach ([$objects[0], $objects[1], $pageStream, $streamObj, $fontObj] as $i => $obj) {
            $offsets[] = $offset;
            $body .= $obj . "\n";
            $offset = strlen($body);
        }

        $xrefOffset = strlen($body);
        $xref = "xref\n0 " . (count($offsets) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $xref .= sprintf("%010d 00000 n \n", $off);
        }

        $trailer = "trailer\n<< /Size " . (count($offsets) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return "%PDF-1.4\n{$body}{$xref}{$trailer}";
    }
}
