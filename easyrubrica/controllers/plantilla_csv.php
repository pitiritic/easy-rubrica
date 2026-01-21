<?php
// easyrubrica/controllers/plantilla_csv.php

// Definir el nombre del archivo con la fecha actual
$filename = "plantilla_usuarios_" . date('Ymd') . ".csv";

// Configurar cabeceras para forzar la descarga del archivo
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '";');

// Abrir el flujo de salida de PHP
$output = fopen('php://output', 'w');

// Insertar la marca de orden de bytes (BOM) para que Excel reconozca UTF-8 (acentos y eñes)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Definir los encabezados SIN el campo curso
// El nuevo orden lógico es: rol; usuario; nombre; email; clase; password
$headers = ['rol', 'usuario', 'nombre', 'email', 'clase', 'password'];

// Escribir la cabecera usando punto y coma como separador (estándar Excel en España)
fputcsv($output, $headers, ';');

// Insertar una fila de ejemplo para guiar al usuario
// Ejemplo: rol; usuario; nombre; email; clase; password
fputcsv($output, ['alumno', 'juan.perez', 'Juan Perez', 'juan@ejemplo.com', '1DAW', '123456'], ';');

fclose($output);
exit;
