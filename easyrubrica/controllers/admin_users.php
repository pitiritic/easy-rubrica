<?php
// controllers/admin_users.php

if (!isset($currentUser) || ($currentUser['rol'] !== 'admin' && $currentUser['rol'] !== 'profesor')) {
    die("Acceso denegado.");
}

$mensaje = "";
$error = "";
$search = $_GET['q'] ?? '';

// --- ORDENACIÓN ---
$allowed_columns = ['usuario', 'nombre', 'rol'];
$order_by = isset($_GET['order_by']) && in_array($_GET['order_by'], $allowed_columns) ? $_GET['order_by'] : 'nombre';
$dir = isset($_GET['dir']) && $_GET['dir'] === 'desc' ? 'DESC' : 'ASC';
$next_dir = ($dir === 'ASC') ? 'desc' : 'asc';

// --- ACCIÓN: DESCARGAR PLANTILLA ---
if (isset($_GET['descargar_plantilla'])) {
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="plantilla_usuarios.csv"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['rol', 'usuario', 'nombre', 'email', 'clase', 'password'], ';');
    fputcsv($output, ['alumno', 'ejemplo01', 'Nombre Alumno', 'alumno@correo.com', 'Clase A', '123456'], ';');
    fclose($output);
    exit;
}

// --- ACCIÓN: IMPORTAR CSV ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importar_csv'])) {
    if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === UPLOAD_ERR_OK) {
        try {
            $file = $_FILES['archivo_csv']['tmp_name'];
            $content = file_get_contents($file);
            $separator = (strpos($content, ';') !== false) ? ';' : ',';
            if (($handle = fopen($file, "r")) !== FALSE) {
                $bom = fread($handle, 3);
                if ($bom != chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
                fgetcsv($handle, 1000, $separator);
                $pdo->beginTransaction();
                $count = 0;
                while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                    if (count($data) < 6) continue;
                    list($rol, $user, $nombre, $email, $cl_nom, $pass) = $data;
                    $stmtC = $pdo->prepare("SELECT id FROM clases WHERE nombre = ? LIMIT 1");
                    $stmtC->execute([$cl_nom]);
                    $clase = $stmtC->fetch();
                    $clase_id = $clase ? $clase['id'] : null;
                    if (!$clase && !empty($cl_nom)) {
                        $stmtNC = $pdo->prepare("INSERT INTO clases (nombre, autor_id) VALUES (?, ?)");
                        $stmtNC->execute([$cl_nom, $currentUser['id']]);
                        $clase_id = $pdo->lastInsertId();
                    }
                    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                    $stmtCheck->execute([$user]);
                    if (!$stmtCheck->fetch()) {
                        $hash = password_hash($pass, PASSWORD_DEFAULT);
                        $pdo->prepare("INSERT INTO usuarios (usuario, nombre, email, rol, password) VALUES (?, ?, ?, ?, ?)")->execute([$user, $nombre, $email, $rol, $hash]);
                        $new_u_id = $pdo->lastInsertId();
                        if ($clase_id) $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$clase_id, $new_u_id]);
                        $count++;
                    }
                }
                $pdo->commit();
                $mensaje = "Importados $count usuarios.";
                fclose($handle);
            }
        } catch (Exception $e) { if($pdo->inTransaction()) $pdo->rollBack(); $error = $e->getMessage(); }
    }
}

// --- ACCIÓN: BORRAR ---
if (isset($_POST['borrar_masivo']) && isset($_POST['usuarios_ids'])) {
    try {
        $ids = $_POST['usuarios_ids'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $mensaje = "Eliminados correctamente.";
    } catch (Exception $e) { $error = $e->getMessage(); }
}

// --- ACCIÓN: CREAR/EDITAR (Simplificadas para brevedad, mantienen tu lógica) ---
if (isset($_POST['crear_usuario'])) {
    try {
        $pdo->beginTransaction();
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, nombre, email, rol, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $_POST['rol'], $pass]);
        $new_id = $pdo->lastInsertId();
        if (!empty($_POST['clases'])) {
            foreach ($_POST['clases'] as $clase_id) { $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$clase_id, $new_id]); }
        }
        $pdo->commit(); $mensaje = "Usuario creado.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

if (isset($_POST['editar_usuario'])) {
    $id_edit = $_POST['id']; $clases = $_POST['clases'] ?? [];
    try {
        $pdo->beginTransaction();
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("UPDATE usuarios SET usuario=?, nombre=?, email=?, rol=?, password=? WHERE id=?");
            $stmt->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $_POST['rol'], password_hash($_POST['password'], PASSWORD_DEFAULT), $id_edit]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET usuario=?, nombre=?, email=?, rol=? WHERE id=?");
            $stmt->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $_POST['rol'], $id_edit]);
        }
        $pdo->prepare("DELETE FROM clase_usuario WHERE usuario_id=?")->execute([$id_edit]);
        foreach ($clases as $clase_id) { $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$clase_id, $id_edit]); }
        $pdo->commit(); $mensaje = "Datos actualizados.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

// --- LISTADO ---
$params = [];
$condiciones = [];

// Seleccionamos solo los campos de la tabla usuarios de forma única
$sql = "SELECT DISTINCT u.id, u.usuario, u.nombre, u.email, u.rol FROM usuarios u";

if ($search) {
    // El uso de EXISTS es clave para no multiplicar filas en el JOIN
    $condiciones[] = "(u.nombre LIKE :s1 OR u.usuario LIKE :s2 OR EXISTS (
        SELECT 1 FROM clase_usuario cu 
        JOIN clases c ON cu.clase_id = c.id 
        WHERE cu.usuario_id = u.id AND c.nombre LIKE :s3
    ))";
    $params['s1'] = "%$search%";
    $params['s2'] = "%$search%";
    $params['s3'] = "%$search%";
}

if (!empty($condiciones)) { $sql .= " WHERE " . implode(" AND ", $condiciones); }
$sql .= " ORDER BY u.$order_by $dir";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enriquecemos los datos en PHP para evitar fallos de SQL
foreach ($usuarios as $key => $u) {
    // 1. Nombres de clases
    $stC = $pdo->prepare("SELECT c.nombre FROM clases c JOIN clase_usuario cu ON c.id = cu.clase_id WHERE cu.usuario_id = ?");
    $stC->execute([$u['id']]);
    $nombres = $stC->fetchAll(PDO::FETCH_COLUMN);
    $usuarios[$key]['clase_nombre'] = $nombres ? implode(', ', $nombres) : '-';

    // 2. IDs de clases
    $stI = $pdo->prepare("SELECT clase_id FROM clase_usuario WHERE usuario_id = ?");
    $stI->execute([$u['id']]);
    $usuarios[$key]['clases_ids'] = $stI->fetchAll(PDO::FETCH_COLUMN);
}

$stmtClases = $pdo->prepare("SELECT * FROM clases");
$stmtClases->execute();
$all_clases = $stmtClases->fetchAll();

require 'views/admin_users.view.php';
