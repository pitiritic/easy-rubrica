<?php
// easyrubrica/controllers/csv_resultados.php
require_once 'config/db.php';

$tarea_id = $_GET['id'] ?? ($_GET['tarea_id'] ?? null);
if (!$tarea_id) die("ID no proporcionado.");

// Lógica de obtención de datos idéntica a pdf_resultados.php (puedes copiar el bloque anterior)
// ... (Obtener $tarea y $alumnos_notas) ...

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Resultados_Tarea_'.$tarea_id.'.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM para Excel
fputcsv($output, ['Alumno', 'Media Hetero', 'Media Coeval', 'Autoevaluación', 'Nota Final']);

foreach ($alumnos_notas as $a) {
    fputcsv($output, [
        $a['nombre'], 
        number_format($a['nota_hetero'], 2), 
        number_format($a['nota_coeval'], 2), 
        number_format($a['nota_auto'], 2), 
        number_format($a['nota_final'], 2)
    ]);
}
fclose($output);
exit;
