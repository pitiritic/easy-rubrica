<?php
// controllers/admin.php
require_once 'libs/Audit.php';

if (!isset($currentUser) || ($currentUser['rol'] !== 'admin' && $currentUser['rol'] !== 'profesor')) {
    die("Acceso denegado.");
}

$mensaje = ""; $error = "";

// A. CREAR TAREA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_tarea'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO clase_rubrica (titulo, clase_id, rubrica_id, autor_id, peso_hetero, peso_coeval, peso_auto, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'activa', NOW())");
        $stmt->execute([trim($_POST['titulo']), (int)$_POST['clase_id'], (int)$_POST['rubrica_id'], $currentUser['id'], (int)$_POST['peso_hetero'], (int)$_POST['peso_coeval'], (int)$_POST['peso_auto']]);
        Audit::log($pdo, "Tarea Asignada", "Rúbrica asignada a clase. Título: " . $_POST['titulo']);
        $mensaje = "Asignación creada con éxito.";
    } catch (Exception $e) { $error = "Error al crear: " . $e->getMessage(); }
}

// B. EDITAR TAREA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_tarea'])) {
    $stmt = $pdo->prepare("UPDATE clase_rubrica SET titulo = ?, peso_hetero = ?, peso_coeval = ?, peso_auto = ? WHERE id = ?");
    $stmt->execute([trim($_POST['titulo']), (int)$_POST['peso_hetero'], (int)$_POST['peso_coeval'], (int)$_POST['peso_auto'], (int)$_POST['id_tarea']]);
    Audit::log($pdo, "Tarea Modificada", "Se actualizó la asignación: " . $_POST['titulo']);
    $mensaje = "Asignación actualizada.";
}

// C. CAMBIAR ESTADO (Bloqueo/Desbloqueo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_estado_id'])) {
    $nuevo_estado = ($_POST['nuevo_estado'] === 'cerrada') ? 'cerrada' : 'activa';
    $stmt = $pdo->prepare("UPDATE clase_rubrica SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, (int)$_POST['toggle_estado_id']]);
    Audit::log($pdo, "Estado Tarea", "Se cambió el estado (candado) de la tarea ID " . $_POST['toggle_estado_id'] . " a " . $nuevo_estado);
}

// D. BORRAR TAREA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_tarea_id'])) {
    $pdo->prepare("DELETE FROM clase_rubrica WHERE id = ?")->execute([(int)$_POST['borrar_tarea_id']]);
    Audit::log($pdo, "Asignación Borrada", "Se eliminó la tarea/asignación ID: " . $_POST['borrar_tarea_id']);
    $mensaje = "Asignación eliminada.";
}

$clases = $pdo->query("SELECT id, nombre FROM clases" . ($currentUser['rol'] === 'profesor' ? " WHERE autor_id = ".$currentUser['id'] : ""))->fetchAll();
$rubricas = $pdo->query("SELECT id, nombre FROM rubricas" . ($currentUser['rol'] === 'profesor' ? " WHERE autor_id = ".$currentUser['id'] : ""))->fetchAll();
$query_list = "SELECT cr.*, c.nombre as nombre_clase, r.nombre as nombre_rubrica FROM clase_rubrica cr JOIN clases c ON cr.clase_id = c.id JOIN rubricas r ON cr.rubrica_id = r.id";
if ($currentUser['rol'] === 'profesor') $query_list .= " WHERE cr.autor_id = " . $currentUser['id'];
$lista_tareas = $pdo->query($query_list . " ORDER BY cr.created_at DESC")->fetchAll();
require 'views/admin.view.php';