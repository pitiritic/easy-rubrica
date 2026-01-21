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
    $_SESSION['vista_clases'] = $_GET['set_vista']; // 'grid' o 'lista'
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
    // A. CREAR CLASE
    if (isset($_POST['crear_clase'])) {
        $nombre = trim($_POST['nombre']);
        $autor_id = $currentUser['id'];
        if ($nombre) {
            $stmt = $pdo->prepare("INSERT INTO clases (nombre, autor_id) VALUES (?, ?)");
            if ($stmt->execute([$nombre, $autor_id])) $mensaje = "Clase creada.";
            else $error = "Error al crear clase.";
        }
    }

    // B. EDITAR CLASE
    if (isset($_POST['editar_clase'])) {
        $id = (int)$_POST['clase_id'];
        $nombre = trim($_POST['nombre']);
        if ($nombre) {
            $stmt = $pdo->prepare("UPDATE clases SET nombre = ? WHERE id = ?");
            if ($stmt->execute([$nombre, $id])) $mensaje = "Clase actualizada.";
        }
    }

    // C. CLONAR CLASE
    if (isset($_POST['clonar_clase'])) {
        $id_org = (int)$_POST['clase_id_original'];
        $nuevo_nom = trim($_POST['nuevo_nombre']);
        $nuevo_aut = (isset($_POST['nuevo_autor_id']) && $currentUser['rol'] === 'admin') ? $_POST['nuevo_autor_id'] : $currentUser['id'];
        
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO clases (nombre, autor_id) VALUES (?, ?)");
            $stmt->execute([$nuevo_nom, $nuevo_aut]);
            $nueva_id = $pdo->lastInsertId();

            if (isset($_POST['copiar_alumnos']) && $nueva_id) {
                $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) 
                               SELECT ?, usuario_id FROM clase_usuario WHERE clase_id = ?")
                    ->execute([$nueva_id, $id_org]);
            }
            $pdo->commit();
            $mensaje = "Clase clonada con éxito.";
        } catch (Exception $e) { 
            $pdo->rollBack(); 
            $error = "Error al clonar: " . $e->getMessage(); 
        }
    }

    // D. VINCULAR ALUMNO
    if (isset($_POST['vincular_alumno'])) {
        $clase_id = (int)$_POST['clase_id'];
        $usuario_id = (int)$_POST['usuario_id'];
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM clase_usuario WHERE clase_id = ? AND usuario_id = ?");
        $stmtCheck->execute([$clase_id, $usuario_id]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)");
            $stmt->execute([$clase_id, $usuario_id]);
            $mensaje = "Alumno vinculado.";
        } else { $error = "El alumno ya está en esta clase."; }
    }

    // E. ELIMINAR VINCULACIÓN
    if (isset($_POST['eliminar_vinculo'])) {
        $clase_id = (int)$_POST['clase_id'];
        $usuario_id = (int)$_POST['usuario_id'];
        $pdo->prepare("DELETE FROM clase_usuario WHERE clase_id = ? AND usuario_id = ?")->execute([$clase_id, $usuario_id]);
        $mensaje = "Alumno desvinculado.";
    }

    // F. ELIMINAR CLASE
    if (isset($_POST['eliminar_clase'])) {
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

if ($currentUser['rol'] === 'profesor' || ($currentUser['rol'] === 'admin' && !$ver_todas)) {
    $query .= " AND c.autor_id = ?";
    $params[] = $currentUser['id'];
}

$stmt = $pdo->prepare($query . " ORDER BY c.nombre ASC");
$stmt->execute($params);
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtAlu = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'alumno' ORDER BY nombre ASC");
$todos_los_alumnos = $stmtAlu->fetchAll();

$profesores = [];
if ($currentUser['rol'] === 'admin') {
    $stmtProf = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol IN ('admin', 'profesor') ORDER BY nombre ASC");
    $profesores = $stmtProf->fetchAll();
}

require 'views/admin_classes.view.php';
