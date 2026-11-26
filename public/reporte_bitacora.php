<?php
// Reporte PDF de la bitácora con FPDF. Recibe por GET los mismos filtros de la pantalla.

// Guarda cualquier salida previa (por ejemplo, líneas en blanco de otros archivos)
// para que no se mezcle con el JSON o el PDF
ob_start();

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/service/BitacoraService.php';
require_once __DIR__ . '/../app/lib/fpdf/fpdf.php';

Sesion::exigirPermiso('BITACORA');

// FPDF trabaja en windows-1252, por eso se convierten las tildes y la ñ
function texto($valor)
{
    return iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $valor);
}

$desde = trim($_GET['desde'] ?? '');
$hasta = trim($_GET['hasta'] ?? '');
$habitacion = trim($_GET['habitacion'] ?? '');

$bitacora = new BitacoraService();
try {
    $eventos = $bitacora->listar($desde, $hasta, $habitacion);
} catch (ReglaNegocioException $e) {
    exit($e->getMessage());
}

// Descripción de los filtros para la cabecera del reporte
$rango = ($desde === '' ? 'el inicio' : date('d/m/Y', strtotime($desde)))
    . ' hasta ' . ($hasta === '' ? 'hoy' : date('d/m/Y', strtotime($hasta)));
$cuarto = $habitacion === '' ? 'todas las habitaciones' : 'habitación ' . $bitacora->numeroDeHabitacion((int) $habitacion);

$pdf = new FPDF('L');
$pdf->AddPage();
$pdf->SetTitle('Bitacora de eventos');

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(277, 10, texto('Bitácora de eventos - Hotel RFID'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(277, 6, texto('Desde ' . $rango . ', ' . $cuarto . '. Generado el ' . date('d/m/Y H:i:s')), 0, 1, 'C');
$pdf->Ln(4);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(217, 226, 243);
$pdf->Cell(38, 8, 'FECHA Y HORA', 1, 0, 'C', true);
$pdf->Cell(42, 8, 'TIPO', 1, 0, 'C', true);
$pdf->Cell(25, 8, texto('HABITACIÓN'), 1, 0, 'C', true);
$pdf->Cell(28, 8, 'UID', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'RESULTADO', 1, 0, 'C', true);
$pdf->Cell(94, 8, 'DETALLE', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
foreach ($eventos as $e) {
    $pdf->Cell(38, 7, date('d/m/Y H:i:s', strtotime($e['fechaHora'])), 1, 0, 'C');
    $pdf->Cell(42, 7, $e['tipo'], 1, 0, 'C');
    $pdf->Cell(25, 7, texto($e['habitacionNumero'] ?? '-'), 1, 0, 'C');
    $pdf->Cell(28, 7, $e['uid'] ?? '-', 1, 0, 'C');
    $pdf->Cell(50, 7, $e['resultado'] ?? '-', 1, 0, 'C');
    $pdf->Cell(94, 7, texto($e['detalle'] ?? ''), 1, 1, 'L');
}

$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(277, 6, 'Total de eventos: ' . count($eventos), 0, 1, 'L');

$pdf->Output('I', 'bitacora.pdf');
