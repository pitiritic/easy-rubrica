<?php
// easyrubrica/controllers/admin.php
require_once 'libs/Audit.php';

if (!isset($currentUser) || ($currentUser['rol'] !== 'admin' && $currentUser['rol'] !== 'profesor')) {
    die("Acceso denegado.");
}

$mensaje = ""; $error = "";
$is_print_mode = false; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A. CREAR TAREA
    if (isset($_POST['crear_tarea'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO clase_rubrica (titulo, clase_id, rubrica_id, autor_id, peso_hetero, peso_coeval, peso_auto, estado, created_at, archivada) VALUES (?, ?, ?, ?, ?, ?, ?, 'activa', NOW(), 0)");
            $stmt->execute([trim($_POST['titulo']), (int)$_POST['clase_id'], (int)$_POST['rubrica_id'], $currentUser['id'], (int)$_POST['peso_hetero'], (int)$_POST['peso_coeval'], (int)$_POST['peso_auto']]);
            Audit::log($pdo, "Tarea Asignada", "Título: " . $_POST['titulo']);
            $mensaje = "Tarea creada con éxito.";
        } catch (Exception $e) { $error = "Error al crear la tarea."; }
    }
    // B. EDITAR TAREA
    if (isset($_POST['editar_tarea'])) {
        try {
            $stmt = $pdo->prepare("UPDATE clase_rubrica SET titulo = ?, peso_hetero = ?, peso_coeval = ?, peso_auto = ? WHERE id = ?");
            $stmt->execute([trim($_POST['titulo']), (int)$_POST['peso_hetero'], (int)$_POST['peso_coeval'], (int)$_POST['peso_auto'], (int)$_POST['id_tarea']]);
            $mensaje = "Tarea actualizada correctamente.";
        } catch (Exception $e) { $error = "Error al actualizar."; }
    }
    // C. CAMBIAR ESTADO
    if (isset($_POST['toggle_estado_id'])) {
        $nuevo_estado = ($_POST['nuevo_estado'] === 'cerrada') ? 'cerrada' : 'activa';
        $stmt = $pdo->prepare("UPDATE clase_rubrica SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, (int)$_POST['toggle_estado_id']]);
    }
    // D. BORRAR TAREA
    if (isset($_POST['borrar_tarea_id'])) {
        $pdo->prepare("DELETE FROM clase_rubrica WHERE id = ?")->execute([(int)$_POST['borrar_tarea_id']]);
        $mensaje = "Tarea eliminada.";
    }
    // E. ARCHIVAR TAREA
    if (isset($_POST['archivar_tarea_id_hidden'])) {
        try {
            $id_tarea = (int)$_POST['archivar_tarea_id_hidden'];
            $curso = trim($_POST['curso_academico'] ?? '');
            $stmt = $pdo->prepare("UPDATE clase_rubrica SET archivada = 1, curso_academico = ? WHERE id = ?");
            $stmt->execute([$curso, $id_tarea]);
            Audit::log($pdo, "Tarea Archivada", "ID: $id_tarea, Curso: $curso");
            $mensaje = "Tarea movida al depósito.";
        } catch (Exception $e) { $error = "Error al archivar."; }
    }
    // F. RECUPERAR TAREA
    if (isset($_POST['recuperar_tarea_id'])) {
        $pdo->prepare("UPDATE clase_rubrica SET archivada = 0 WHERE id = ?")->execute([(int)$_POST['recuperar_tarea_id']]);
        $mensaje = "Tarea recuperada.";
    }
}

$clases = $pdo->query("SELECT id, nombre FROM clases" . ($currentUser['rol'] === 'profesor' ? " WHERE autor_id = ".$currentUser['id'] : ""))->fetchAll();
$rubricas = $pdo->query("SELECT id, nombre FROM rubricas ORDER BY nombre ASC")->fetchAll();
$query_base = "SELECT cr.*, c.nombre as nombre_clase, r.nombre as nombre_rubrica FROM clase_rubrica cr JOIN clases c ON cr.clase_id = c.id JOIN rubricas r ON cr.rubrica_id = r.id";
$where = ($currentUser['rol'] === 'profesor') ? " WHERE cr.autor_id = " . $currentUser['id'] : " WHERE 1=1 ";
$lista_tareas = $pdo->query($query_base . $where . " AND cr.archivada = 0 ORDER BY cr.created_at DESC")->fetchAll();
$deposito_tareas = $pdo->query($query_base . $where . " AND cr.archivada = 1 ORDER BY cr.curso_academico DESC, cr.created_at DESC")->fetchAll();

require 'views/admin.view.php';