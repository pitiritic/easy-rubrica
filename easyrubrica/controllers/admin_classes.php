<?php
/**
 * Easy Rúbrica - Controlador de Gestión de Clases
 */

if (!isset($currentUser) || ($currentUser['rol'] != 'admin' && $currentUser['rol'] != 'profesor')) {
    die("Acceso denegado.");
}

$mensaje = ""; 
$error = "";

// --- GESTIÓN DE VISTAS Y FILTROS ---
if (isset($_GET['set_vista'])) {
    $_SESSION['vista_clases'] = $_GET['set_vista']; 
    header("Location: ?action=gestion_clases_lista");
    exit;
}
$vista_actual = $_SESSION['vista_clases'] ?? 'grid';

if (isset($_GET['toggle_ver_todas']) && $currentUser['rol'] === 'admin') {
    $_SESSION['ver_todas_clases'] = ($_GET['toggle_ver_todas'] == '1');
    header("Location: ?action=gestion_clases_lista");
    exit;
}
$ver_todas = $_SESSION['ver_todas_clases'] ?? false;

// --- 1. PROCESAR ACCIONES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Función de seguridad: Verificar si la clase pertenece al usuario o es admin
    $esDuenio = function($clase_id) use ($pdo, $currentUser) {
        if ($currentUser['rol'] === 'admin') return true;
        $st = $pdo->prepare("SELECT 1 FROM clases WHERE id = ? AND autor_id = ?");
        $st->execute([$clase_id, $currentUser['id']]);
        return (bool)$st->fetch();
    };

    // A. CREAR CLASE
    if (isset($_POST['crear_clase'])) {
        $nombre = trim($_POST['nombre']);
        if ($nombre) {
            $pdo->prepare("INSERT INTO clases (nombre, autor_id) VALUES (?, ?)")->execute([$nombre, $currentUser['id']]);
            $mensaje = "Clase creada.";
        }
    }

    // B. EDITAR NOMBRE
    if (isset($_POST['editar_clase']) && $esDuenio($_POST['clase_id'])) {
        $id = (int)$_POST['clase_id'];
        $nombre = trim($_POST['nombre']);
        $pdo->prepare("UPDATE clases SET nombre = ? WHERE id = ?")->execute([$nombre, $id]);
        $mensaje = "Nombre actualizado.";
    }

    // C. CLONAR CLASE
    if (isset($_POST['clonar_clase']) && $esDuenio($_POST['clase_id_original'])) {
        $id_origen = (int)$_POST['clase_id_original'];
        $nuevo_nombre = trim($_POST['nuevo_nombre']);
        $nuevo_autor = ($currentUser['rol'] === 'admin' && isset($_POST['nuevo_autor_id'])) ? $_POST['nuevo_autor_id'] : $currentUser['id'];
        
        $pdo->prepare("INSERT INTO clases (nombre, autor_id) VALUES (?, ?)")->execute([$nuevo_nombre, $nuevo_autor]);
        $nuevo_id = $pdo->lastInsertId();
        
        if (isset($_POST['copiar_alumnos'])) {
            $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) SELECT ?, usuario_id FROM clase_usuario WHERE clase_id = ?")->execute([$nuevo_id, $id_origen]);
        }
        $mensaje = "Clase clonada.";
    }

    // D. VINCULAR ALUMNO
    if (isset($_POST['vincular_alumno']) && $esDuenio($_POST['clase_id'])) {
        $clase_id = (int)$_POST['clase_id'];
        $usuario_id = (int)$_POST['usuario_id'];
        $st = $pdo->prepare("SELECT 1 FROM clase_usuario WHERE clase_id = ? AND usuario_id = ?");
        $st->execute([$clase_id, $usuario_id]);
        if (!$st->fetch()) {
            $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$clase_id, $usuario_id]);
            $mensaje = "Alumno vinculado.";
        }
    }

    // E. DESVINCULAR ALUMNO (Eliminar vínculo)
    if (isset($_POST['eliminar_vinculo']) && $esDuenio($_POST['clase_id'])) {
        $clase_id = (int)$_POST['clase_id'];
        $usuario_id = (int)$_POST['usuario_id'];
        $pdo->prepare("DELETE FROM clase_usuario WHERE clase_id = ? AND usuario_id = ?")->execute([$clase_id, $usuario_id]);
        $mensaje = "Alumno desvinculado.";
    }

    // F. ELIMINAR CLASE
    if (isset($_POST['eliminar_clase']) && $esDuenio($_POST['clase_id'])) {
        $id = (int)$_POST['clase_id'];
        $pdo->prepare("DELETE FROM clases WHERE id = ?")->execute([$id]);
        $mensaje = "Clase eliminada.";
    }
}

// --- 2. OBTENER DATOS ---
$search = $_GET['q'] ?? '';
$query = "SELECT c.*, COALESCE(u.nombre, 'Sistema') as autor_nombre, 
          (SELECT COUNT(*) FROM clase_usuario cu WHERE cu.clase_id = c.id) as num_alumnos 
          FROM clases c LEFT JOIN usuarios u ON c.autor_id = u.id WHERE c.nombre LIKE ?";
$params = ["%$search%"];

if ($currentUser['rol'] === 'profesor') {
    $query .= " AND c.autor_id = ?";
    $params[] = $currentUser['id'];
} elseif ($currentUser['rol'] === 'admin' && !$ver_todas) {
    $query .= " AND (c.autor_id = ? OR c.autor_id IS NULL)";
    $params[] = $currentUser['id'];
}

$stmt = $pdo->prepare($query . " ORDER BY c.nombre ASC");
$stmt->execute($params);
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Datos para modales (Todos los alumnos y lista de profesores si es admin)
$todos_los_alumnos = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'alumno' ORDER BY nombre ASC")->fetchAll();
$profesores = ($currentUser['rol'] === 'admin') ? $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'profesor' OR rol = 'admin' ORDER BY nombre ASC")->fetchAll() : [];

require 'views/admin_classes.view.php';