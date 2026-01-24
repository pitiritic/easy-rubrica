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
            if (($handle = fopen($file, "r")) !== FALSE) {
                $bom = fread($handle, 3);
                if ($bom != chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
                $separator = (strpos(fgets($handle), ';') !== false) ? ';' : ',';
                rewind($handle);
                if ($bom != chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
                fgetcsv($handle, 1000, $separator);
                
                $pdo->beginTransaction();
                $count = 0;
                while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
                    if (count($data) < 6) continue;
                    list($rol, $user, $nombre, $email, $cl_nom, $pass) = $data;
                    if ($currentUser['rol'] === 'profesor') $rol = 'alumno';
                    
                    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                    $stmtCheck->execute([$user]);
                    if (!$stmtCheck->fetch()) {
                        $hash = password_hash($pass, PASSWORD_DEFAULT);
                        $pdo->prepare("INSERT INTO usuarios (usuario, nombre, email, rol, password, creador_id) VALUES (?, ?, ?, ?, ?, ?)")
                            ->execute([$user, $nombre, $email, $rol, $hash, $currentUser['id']]);
                        $count++;
                    }
                }
                $pdo->commit(); $mensaje = "Importados $count usuarios."; fclose($handle);
            }
        } catch (Exception $e) { if($pdo->inTransaction()) $pdo->rollBack(); $error = $e->getMessage(); }
    }
}

// --- ACCIÓN: BORRAR ---
if (isset($_POST['borrar_masivo']) && isset($_POST['usuarios_ids'])) {
    try {
        $ids = $_POST['usuarios_ids'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($currentUser['rol'] === 'profesor') {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id IN ($placeholders) AND creador_id = ? AND rol = 'alumno'");
            $params = array_merge($ids, [$currentUser['id']]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id IN ($placeholders)");
            $params = $ids;
        }
        $stmt->execute($params);
        $mensaje = "Usuarios eliminados correctamente.";
    } catch (Exception $e) { $error = $e->getMessage(); }
}

// --- ACCIÓN: CREAR ---
if (isset($_POST['crear_usuario'])) {
    try {
        $pdo->beginTransaction();
        $rol_final = $_POST['rol'];
        if ($currentUser['rol'] === 'profesor') $rol_final = 'alumno';
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, nombre, email, rol, password, creador_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_final, $pass, $currentUser['id']]);
        $new_id = $pdo->lastInsertId();
        if (!empty($_POST['clases'])) {
            foreach ($_POST['clases'] as $clase_id) { 
                $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$clase_id, $new_id]); 
            }
        }
        $pdo->commit(); $mensaje = "Usuario creado.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

// --- ACCIÓN: EDITAR ---
if (isset($_POST['editar_usuario'])) {
    $id_edit = $_POST['id']; 
    $clases_seleccionadas = $_POST['clases'] ?? [];
    
    $stmtT = $pdo->prepare("SELECT rol, creador_id FROM usuarios WHERE id = ?");
    $stmtT->execute([$id_edit]);
    $target = $stmtT->fetch();
    
    $puede_editar_perfil = ($currentUser['rol'] === 'admin' || ($target && $target['creador_id'] == $currentUser['id']));

    try {
        $pdo->beginTransaction();
        
        // 1. Actualizar datos de perfil si tiene permiso
        if ($puede_editar_perfil) {
            $rol_final = ($currentUser['rol'] === 'profesor') ? 'alumno' : $_POST['rol'];
            if (!empty($_POST['password'])) {
                $pdo->prepare("UPDATE usuarios SET usuario=?, nombre=?, email=?, rol=?, password=? WHERE id=?")
                    ->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_final, password_hash($_POST['password'], PASSWORD_DEFAULT), $id_edit]);
            } else {
                $pdo->prepare("UPDATE usuarios SET usuario=?, nombre=?, email=?, rol=? WHERE id=?")
                    ->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_final, $id_edit]);
            }
        }

        // 2. Gestión de Clases (Lógica de no interferencia)
        if ($currentUser['rol'] === 'admin') {
            // El admin sí limpia todo y reasigna
            $pdo->prepare("DELETE FROM clase_usuario WHERE usuario_id=?")->execute([$id_edit]);
            foreach ($clases_seleccionadas as $clase_id) {
                $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$clase_id, $id_edit]);
            }
        } else {
            // El profesor solo gestiona SUS clases
            // Buscamos cuáles son las clases del profesor actual
            $stP = $pdo->prepare("SELECT id FROM clases WHERE autor_id = ?");
            $stP->execute([$currentUser['id']]);
            $mis_clases_ids = $stP->fetchAll(PDO::FETCH_COLUMN);

            // Quitamos al alumno solo de mis clases que NO están marcadas ahora
            if (!empty($mis_clases_ids)) {
                $placeholders = implode(',', array_fill(0, count($mis_clases_ids), '?'));
                $pdo->prepare("DELETE FROM clase_usuario WHERE usuario_id = ? AND clase_id IN ($placeholders)")
                    ->execute(array_merge([$id_edit], $mis_clases_ids));
                
                // Añadimos al alumno a las seleccionadas (siempre que sean del profesor)
                foreach ($clases_seleccionadas as $clase_id) {
                    if (in_array($clase_id, $mis_clases_ids)) {
                        $pdo->prepare("INSERT IGNORE INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")
                            ->execute([$clase_id, $id_edit]);
                    }
                }
            }
        }

        $pdo->commit(); $mensaje = "Cambios guardados correctamente.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

// --- LISTADO ---
$sql = "SELECT DISTINCT u.id, u.usuario, u.nombre, u.email, u.rol, u.creador_id FROM usuarios u";
$params = [];
if ($search) {
    $sql .= " WHERE u.nombre LIKE :s1 OR u.usuario LIKE :s2";
    $params = ['s1' => "%$search%", 's2' => "%$search%"];
}
$sql .= " ORDER BY u.$order_by $dir";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $key => $u) {
    $stC = $pdo->prepare("SELECT c.nombre FROM clases c JOIN clase_usuario cu ON c.id = cu.clase_id WHERE cu.usuario_id = ?");
    $stC->execute([$u['id']]);
    $usuarios[$key]['clase_nombre'] = implode(', ', $stC->fetchAll(PDO::FETCH_COLUMN)) ?: '-';
    $stI = $pdo->prepare("SELECT clase_id FROM clase_usuario WHERE usuario_id = ?");
    $stI->execute([$u['id']]);
    $usuarios[$key]['clases_ids'] = $stI->fetchAll(PDO::FETCH_COLUMN);
}

$all_clases = $pdo->query("SELECT * FROM clases")->fetchAll();
require 'views/admin_users.view.php';