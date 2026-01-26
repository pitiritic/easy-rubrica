<?php
// controllers/admin_users.php

if (!isset($currentUser) || ($currentUser['rol'] !== 'admin' && $currentUser['rol'] !== 'profesor')) {
    die("Acceso denegado.");
}

$mensaje = "";
$error = "";
$search = $_GET['q'] ?? '';

// --- ORDENACIÓN ---
$allowed_columns = ['usuario', 'nombre', 'rol', 'clase_nombre'];
$order_by = isset($_GET['order_by']) && in_array($_GET['order_by'], $allowed_columns) ? $_GET['order_by'] : 'nombre';
$dir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc') ? 'DESC' : 'ASC';

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
                $linea1 = fgets($handle);
                $sep = (strpos($linea1, ';') !== false) ? ';' : ',';
                rewind($handle);
                if ($bom != chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
                fgetcsv($handle, 1000, $sep); 
                
                $pdo->beginTransaction();
                $count = 0;
                while (($data = fgetcsv($handle, 1000, $sep)) !== FALSE) {
                    if (count($data) < 6) continue;
                    list($rol, $user, $nombre, $email, $cl_nom, $pass) = $data;
                    if ($currentUser['rol'] === 'profesor') $rol = 'alumno';
                    
                    $stCheck = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                    $stCheck->execute([$user]);
                    if (!$stCheck->fetch()) {
                        $hash = password_hash($pass, PASSWORD_DEFAULT);
                        $pdo->prepare("INSERT INTO usuarios (usuario, nombre, email, rol, password, creador_id) VALUES (?, ?, ?, ?, ?, ?)")
                            ->execute([$user, $nombre, $email, $rol, $hash, $currentUser['id']]);
                        $new_id = $pdo->lastInsertId();

                        if (!empty(trim($cl_nom))) {
                            $stCl = $pdo->prepare("SELECT id FROM clases WHERE nombre = ?");
                            $stCl->execute([trim($cl_nom)]);
                            $cid = $stCl->fetchColumn();
                            if ($cid) $pdo->prepare("INSERT IGNORE INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$cid, $new_id]);
                        }
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
        $ids = array_filter($_POST['usuarios_ids'], fn($id) => $id != $currentUser['id']);
        if (!empty($ids)) {
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
        }
    } catch (Exception $e) { $error = $e->getMessage(); }
}

// --- ACCIÓN: CREAR ---
if (isset($_POST['crear_usuario'])) {
    try {
        $pdo->beginTransaction();
        $rol_f = ($currentUser['rol'] === 'profesor') ? 'alumno' : $_POST['rol'];
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO usuarios (usuario, nombre, email, rol, password, creador_id) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_f, $hash, $currentUser['id']]);
        $nid = $pdo->lastInsertId();
        if (!empty($_POST['clases'])) {
            foreach ($_POST['clases'] as $cid) $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$cid, $nid]);
        }
        $pdo->commit(); $mensaje = "Usuario creado correctamente.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

// --- ACCIÓN: EDITAR ---
if (isset($_POST['editar_usuario'])) {
    try {
        $pdo->beginTransaction();
        $id_e = $_POST['id'];
        $stT = $pdo->prepare("SELECT rol, creador_id FROM usuarios WHERE id = ?");
        $stT->execute([$id_e]);
        $target = $stT->fetch();
        
        if ($currentUser['rol'] === 'admin' || ($target && $target['creador_id'] == $currentUser['id'])) {
            $rol_f = ($currentUser['rol'] === 'profesor') ? 'alumno' : $_POST['rol'];
            if (!empty($_POST['password'])) {
                $pdo->prepare("UPDATE usuarios SET usuario=?, nombre=?, email=?, rol=?, password=? WHERE id=?")
                    ->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_f, password_hash($_POST['password'], PASSWORD_DEFAULT), $id_e]);
            } else {
                $pdo->prepare("UPDATE usuarios SET usuario=?, nombre=?, email=?, rol=? WHERE id=?")
                    ->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_f, $id_e]);
            }
        }

        if ($currentUser['rol'] === 'admin') {
            $pdo->prepare("DELETE FROM clase_usuario WHERE usuario_id=?")->execute([$id_e]);
            if (!empty($_POST['clases'])) {
                foreach ($_POST['clases'] as $cid) $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$cid, $id_e]);
            }
        } else {
            $stP = $pdo->prepare("SELECT id FROM clases WHERE autor_id = ?");
            $stP->execute([$currentUser['id']]);
            $mis_clases = $stP->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($mis_clases)) {
                $placeholders = implode(',', array_fill(0, count($mis_clases), '?'));
                $pdo->prepare("DELETE FROM clase_usuario WHERE usuario_id = ? AND clase_id IN ($placeholders)")
                    ->execute(array_merge([$id_e], $mis_clases));
                if (!empty($_POST['clases'])) {
                    foreach ($_POST['clases'] as $cid) {
                        if (in_array($cid, $mis_clases)) $pdo->prepare("INSERT IGNORE INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$cid, $id_e]);
                    }
                }
            }
        }
        $pdo->commit(); $mensaje = "Cambios guardados.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

// --- LISTADO ---
$sql = "SELECT u.id, u.usuario, u.nombre, u.email, u.rol, u.creador_id, 
        (SELECT GROUP_CONCAT(c.nombre SEPARATOR ', ') FROM clases c JOIN clase_usuario cu ON c.id = cu.clase_id WHERE cu.usuario_id = u.id) as clase_nombre
        FROM usuarios u ORDER BY $order_by $dir";
$usuarios = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $k => $u) {
    $stI = $pdo->prepare("SELECT clase_id FROM clase_usuario WHERE usuario_id = ?");
    $stI->execute([$u['id']]);
    $usuarios[$k]['clases_ids'] = $stI->fetchAll(PDO::FETCH_COLUMN);
}
$all_clases = $pdo->query("SELECT * FROM clases ORDER BY nombre ASC")->fetchAll();
require 'views/admin_users.view.php';