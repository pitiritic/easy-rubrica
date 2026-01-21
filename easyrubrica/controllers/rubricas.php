<?php
// controllers/rubricas.php

if (!isset($currentUser) || $currentUser['rol'] == 'alumno') {
    die("Acceso denegado.");
}

$mensaje = ""; 
$error = "";
$mode = isset($_GET['edit_id']) ? 'edit' : 'create';

// --- 1. DESCARGAR RÚBRICA ESPECÍFICA EN CSV ---
if (isset($_GET['export_csv_id'])) {
    $id_csv = (int)$_GET['export_csv_id'];
    $stmtR = $pdo->prepare("SELECT * FROM rubricas WHERE id = ?");
    $stmtR->execute([$id_csv]);
    $rub = $stmtR->fetch();
    if ($rub) {
        $stmtC = $pdo->prepare("SELECT * FROM criterios WHERE rubrica_id = ?");
        $stmtC->execute([$id_csv]);
        $criterios_csv = $stmtC->fetchAll();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="export_rubrica_'.urlencode($rub['nombre']).'.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, array('Nombre Rubrica', 'Descripcion', 'Asignatura', 'Competencias', 'Criterio', 'Nivel 1 (Insuficiente)', 'Nivel 2 (Aceptable)', 'Nivel 3 (Bueno)', 'Nivel 4 (Excelente)'));
        $comps_str = implode(',', json_decode($rub['competencias'], true) ?: []);
        foreach ($criterios_csv as $c) {
            $stmtN = $pdo->prepare("SELECT * FROM niveles WHERE criterio_id = ? ORDER BY valor ASC");
            $stmtN->execute([$c['id']]);
            $niveles = $stmtN->fetchAll();
            $d = [1 => '', 2 => '', 3 => '', 4 => ''];
            foreach ($niveles as $n) { $d[$n['valor']] = $n['descriptor']; }
            fputcsv($output, array($rub['nombre'], $rub['descripcion'], $rub['asignatura'], $comps_str, $c['nombre'], $d[1], $d[2], $d[3], $d[4]));
        }
        fclose($output);
        exit;
    }
}

// --- 2. ACCIÓN: GUARDAR O ACTUALIZAR RÚBRICA ---
if (isset($_POST['guardar_rubrica'])) {
    $nombre = $_POST['nombre'] ?? '';
    $asignatura = $_POST['asignatura'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $competencias_input = $_POST['competencias_hidden'] ?? '';
    $competencias_array = array_filter(array_map('trim', explode(',', $competencias_input)));
    $competencias_json = json_encode(array_values($competencias_array));
    try {
        $pdo->beginTransaction();
        if (isset($_POST['id_rubrica']) && !empty($_POST['id_rubrica'])) {
            $id_rubrica = (int)$_POST['id_rubrica'];
            $pdo->prepare("UPDATE rubricas SET nombre = ?, descripcion = ?, asignatura = ?, competencias = ? WHERE id = ?")->execute([$nombre, $descripcion, $asignatura, $competencias_json, $id_rubrica]);
            $pdo->prepare("DELETE FROM criterios WHERE rubrica_id = ?")->execute([$id_rubrica]);
        } else {
            $pdo->prepare("INSERT INTO rubricas (nombre, descripcion, asignatura, competencias, autor_id) VALUES (?, ?, ?, ?, ?)")->execute([$nombre, $descripcion, $asignatura, $competencias_json, $currentUser['id']]);
            $id_rubrica = $pdo->lastInsertId();
        }
        if (isset($_POST['criterio']) && is_array($_POST['criterio'])) {
            foreach ($_POST['criterio'] as $index => $nombre_criterio) {
                if (empty(trim($nombre_criterio))) continue;
                $stmtC = $pdo->prepare("INSERT INTO criterios (rubrica_id, nombre) VALUES (?, ?)");
                $stmtC->execute([$id_rubrica, $nombre_criterio]);
                $id_criterio = $pdo->lastInsertId();
                $etiquetas = [1 => 'Insuficiente', 2 => 'Aceptable', 3 => 'Bueno', 4 => 'Excelente'];
                for ($i = 1; $i <= 4; $i++) {
                    $desc_nivel = $_POST["desc_{$index}_{$i}"] ?? '';
                    $pdo->prepare("INSERT INTO niveles (criterio_id, valor, etiqueta, descriptor) VALUES (?, ?, ?, ?)")->execute([$id_criterio, $i, $etiquetas[$i], $desc_nivel]);
                }
            }
        }
        $pdo->commit();
        header("Location: ?action=rubricas&msg=" . urlencode("Operación exitosa"));
        exit;
    } catch (Exception $e) { $pdo->rollBack(); $error = "Error: " . $e->getMessage(); }
}

// --- 3. ACCIÓN: IMPORTACIÓN CSV ---
if (isset($_POST['importar_csv'])) {
    if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === UPLOAD_ERR_OK) {
        $handle = fopen($_FILES['archivo_csv']['tmp_name'], "r");
        fgetcsv($handle); 
        try {
            $pdo->beginTransaction();
            $rub_id = null; $nom_ant = "";
            while (($row = fgetcsv($handle)) !== false) {
                if (empty(trim($row[0]))) continue;
                if (trim($row[0]) !== $nom_ant) {
                    $stmt = $pdo->prepare("INSERT INTO rubricas (nombre, descripcion, asignatura, competencias, autor_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute(array(trim($row[0]), trim($row[1]), trim($row[2]), json_encode(explode(',', $row[3])), $currentUser['id']));
                    $rub_id = $pdo->lastInsertId(); $nom_ant = trim($row[0]);
                }
                $pdo->prepare("INSERT INTO criterios (rubrica_id, nombre) VALUES (?, ?)")->execute(array($rub_id, trim($row[4])));
                $cid = $pdo->lastInsertId();
                $lbls = array(1=>'Insuficiente', 2=>'Aceptable', 3=>'Bueno', 4=>'Excelente');
                for($i=1; $i<=4; $i++) { $pdo->prepare("INSERT INTO niveles (criterio_id, valor, etiqueta, descriptor) VALUES (?, ?, ?, ?)")->execute(array($cid, $i, $lbls[$i], trim($row[4+$i]))); }
            }
            $pdo->commit(); $mensaje = "Importación exitosa. <script>setTimeout(() => { let a = document.querySelector('.alert-success'); if(a) new bootstrap.Alert(a).close(); }, 3000);</script>";
        } catch (Exception $e) { $pdo->rollBack(); $error = "Error: " . $e->getMessage(); }
    }
}

// --- 4. ACCIONES VARIAS ---
if (isset($_POST['borrar_rubrica_id'])) {
    $pdo->prepare("DELETE FROM rubricas WHERE id = ?")->execute([(int)$_POST['borrar_rubrica_id']]);
}

if (isset($_POST['duplicar_rubrica_id'])) {
    $id_org = (int)$_POST['duplicar_rubrica_id'];
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM rubricas WHERE id = ?");
        $stmt->execute([$id_org]);
        $r = $stmt->fetch();
        if ($r) {
            $stmtInsert = $pdo->prepare("INSERT INTO rubricas (nombre, descripcion, asignatura, competencias, autor_id) VALUES (?, ?, ?, ?, ?)");
            $stmtInsert->execute([$r['nombre'] . ' (Copia)', $r['descripcion'], $r['asignatura'], $r['competencias'], $currentUser['id']]);
            $nueva_id = $pdo->lastInsertId();
            $stmtC = $pdo->prepare("SELECT * FROM criterios WHERE rubrica_id = ?");
            $stmtC->execute([$id_org]);
            $criterios = $stmtC->fetchAll();
            foreach ($criterios as $crit) {
                $stmtInsCrit = $pdo->prepare("INSERT INTO criterios (rubrica_id, nombre) VALUES (?, ?)");
                $stmtInsCrit->execute([$nueva_id, $crit['nombre']]);
                $nuevo_crit_id = $pdo->lastInsertId();
                $stmtN = $pdo->prepare("SELECT * FROM niveles WHERE criterio_id = ?");
                $stmtN->execute([$crit['id']]);
                $niveles = $stmtN->fetchAll();
                foreach ($niveles as $niv) {
                    $stmtInsNiv = $pdo->prepare("INSERT INTO niveles (criterio_id, valor, etiqueta, descriptor) VALUES (?, ?, ?, ?)");
                    $stmtInsNiv->execute([$nuevo_crit_id, $niv['valor'], $niv['etiqueta'], $niv['descriptor']]);
                }
            }
        }
        $pdo->commit();
        $mensaje = "Rúbrica clonada correctamente. <script>setTimeout(() => { let a = document.querySelector('.alert-success'); if(a) new bootstrap.Alert(a).close(); }, 3000);</script>";
    } catch (Exception $e) { $pdo->rollBack(); $error = "Error al clonar: " . $e->getMessage(); }
}

// --- 5. CARGAR DATOS ---
if (isset($_GET['msg'])) { 
    $mensaje = htmlspecialchars($_GET['msg']) . " <script>setTimeout(() => { let a = document.querySelector('.alert-success'); if(a) new bootstrap.Alert(a).close(); }, 3000);</script>"; 
}

if ($mode === 'edit' && isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM rubricas WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_id']]);
    $rubrica_a_editar = $stmt->fetch();
    if ($rubrica_a_editar) {
        $stmtC = $pdo->prepare("SELECT * FROM criterios WHERE rubrica_id = ?");
        $stmtC->execute([(int)$_GET['edit_id']]);
        $criterios = $stmtC->fetchAll();
        foreach ($criterios as $key => $crit) {
            $stmtN = $pdo->prepare("SELECT * FROM niveles WHERE criterio_id = ? ORDER BY valor ASC");
            $stmtN->execute([$crit['id']]);
            $criterios[$key]['niveles'] = $stmtN->fetchAll();
        }
        $rubrica_a_editar['criterios'] = $criterios;
        $comps = json_decode($rubrica_a_editar['competencias'] ?? '[]', true);
        $rubrica_a_editar['competencias_string'] = is_array($comps) ? implode(',', $comps) : '';
    }
}

$lista_rubricas = $pdo->query("SELECT r.*, u.nombre as autor_nombre FROM rubricas r LEFT JOIN usuarios u ON u.id = r.autor_id ORDER BY r.id DESC")->fetchAll();
foreach ($lista_rubricas as $key => $r) {
    $lista_rubricas[$key]['lista_competencias'] = json_decode($r['competencias'] ?? '[]', true) ?: [];
    $stmtC = $pdo->prepare("SELECT * FROM criterios WHERE rubrica_id = ?");
    $stmtC->execute([$r['id']]);
    $crits = $stmtC->fetchAll();
    foreach($crits as $ck => $cv) {
        $stmtN = $pdo->prepare("SELECT * FROM niveles WHERE criterio_id = ? ORDER BY valor ASC");
        $stmtN->execute([$cv['id']]);
        $crits[$ck]['niveles'] = $stmtN->fetchAll();
    }
    $lista_rubricas[$key]['datos_completos'] = $crits;
}
$asignaturas_disponibles = $pdo->query("SELECT DISTINCT asignatura FROM rubricas WHERE asignatura!=''")->fetchAll(PDO::FETCH_COLUMN);

require 'views/rubricas.view.php';
