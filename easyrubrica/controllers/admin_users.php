<?php
// easyrubrica/controllers/admin_users.php
require_once 'libs/Audit.php';

if (!isset($currentUser) || ($currentUser['rol'] !== 'admin' && $currentUser['rol'] !== 'profesor')) {
    die("Acceso denegado.");
}

$mensaje = ""; $error = "";
$search = $_GET['q'] ?? '';

// --- CONFIGURACIÓN DE ORDENACIÓN ---
$allowed_columns = ['usuario', 'nombre', 'rol', 'clase_nombre'];
$order_by = (isset($_GET['order_by']) && in_array($_GET['order_by'], $allowed_columns)) ? $_GET['order_by'] : 'nombre';
$dir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc') ? 'DESC' : 'ASC';

// 1. DESCARGAR PLANTILLA
if (isset($_GET['descargar_plantilla'])) {
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="plantilla_usuarios.csv"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['rol', 'usuario', 'nombre', 'email', 'clase', 'password'], ';');
    fputcsv($output, ['alumno', 'ejemplo01', 'Nombre Alumno', 'alumno@correo.com', 'Clase A', '123456'], ';');
    fclose($output); exit;
}

// 2. IMPORTACIÓN MASIVA CORREGIDA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importar_csv'])) {
    if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === UPLOAD_ERR_OK) {
        try {
            $handle = fopen($_FILES['archivo_csv']['tmp_name'], "r");
            
            // Gestión de BOM para evitar errores en la primera columna
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
            $start_pos = ftell($handle);

            // Detectar separador
            $linea1 = fgets($handle);
            $sep = (strpos($linea1, ';') !== false) ? ';' : ',';
            
            // Volver al inicio de los datos (justo después del BOM si existe)
            fseek($handle, $start_pos);
            fgetcsv($handle, 1000, $sep); // Saltar cabecera

            $pdo->beginTransaction(); 
            $count = 0;
            while (($data = fgetcsv($handle, 1000, $sep)) !== FALSE) {
                if (count($data) < 6 || empty(trim($data[1]))) continue;
                $row = array_map('trim', $data);
                list($rol, $user, $nombre, $email, $cl_nom, $pass) = $row;
                if ($currentUser['rol'] === 'profesor') $rol = 'alumno';
                
                $stCheck = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                $stCheck->execute([$user]);
                $existente = $stCheck->fetch();

                if (!$existente) {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $pdo->prepare("INSERT INTO usuarios (usuario, nombre, email, rol, password, creador_id, activo) VALUES (?, ?, ?, ?, ?, ?, 1)")
                        ->execute([$user, $nombre, $email, $rol, $hash, $currentUser['id']]);
                    $target_id = $pdo->lastInsertId();
                    $count++;
                } else {
                    $target_id = $existente['id'];
                    $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE id = ?")->execute([$target_id]);
                    $count++;
                }

                // VINCULACIÓN DE CLASE CORREGIDA
                if (!empty($cl_nom)) {
                    $stCl = $pdo->prepare("SELECT id FROM clases WHERE nombre = ?");
                    $stCl->execute([$cl_nom]);
                    $cid = $stCl->fetchColumn();
                    
                    // Si la clase no existe, se crea automáticamente
                    if (!$cid) {
                        $pdo->prepare("INSERT INTO clases (nombre) VALUES (?)")->execute([$cl_nom]);
                        $cid = $pdo->lastInsertId();
                    }

                    if ($cid) {
                        $pdo->prepare("INSERT IGNORE INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")
                            ->execute([$cid, $target_id]);
                    }
                }
            }
            $pdo->commit(); 
            Audit::log($pdo, "Importación Masiva", "Se importaron $count usuarios.");
            $mensaje = "Importados $count usuarios."; fclose($handle);
        } catch (Exception $e) { if($pdo->inTransaction()) $pdo->rollBack(); $error = $e->getMessage(); }
    }
}

// 3. ACCIONES: BORRAR, CREAR Y EDITAR
if (isset($_POST['borrar_masivo']) && isset($_POST['usuarios_ids'])) {
    try {
        $ids = array_filter($_POST['usuarios_ids'], fn($id) => $id != $currentUser['id']);
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql_del = ($currentUser['rol'] === 'profesor') ? "UPDATE usuarios SET activo = 0 WHERE id IN ($placeholders) AND creador_id = ? AND rol = 'alumno'" : "UPDATE usuarios SET activo = 0 WHERE id IN ($placeholders)";
            $params = ($currentUser['rol'] === 'profesor') ? array_merge($ids, [$currentUser['id']]) : $ids;
            $pdo->prepare($sql_del)->execute($params);
            Audit::log($pdo, "Usuario Desactivado", "IDs: " . implode(',', $ids));
            $mensaje = "Usuarios eliminados correctamente.";
        }
    } catch (Exception $e) { $error = $e->getMessage(); }
}

if (isset($_POST['crear_usuario'])) {
    try {
        $pdo->beginTransaction();
        $rol_f = ($currentUser['rol'] === 'profesor') ? 'alumno' : $_POST['rol'];
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO usuarios (usuario, nombre, email, rol, password, creador_id, activo) VALUES (?, ?, ?, ?, ?, ?, 1)")
            ->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_f, $hash, $currentUser['id']]);
        $nid = $pdo->lastInsertId();
        if (!empty($_POST['clases'])) { foreach ($_POST['clases'] as $cid) $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$cid, $nid]); }
        $pdo->commit(); $mensaje = "Usuario creado correctamente.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

if (isset($_POST['editar_usuario'])) {
    try {
        $pdo->beginTransaction(); $id_e = $_POST['id'];
        $stT = $pdo->prepare("SELECT creador_id FROM usuarios WHERE id = ?"); $stT->execute([$id_e]); $creador = $stT->fetchColumn();
        if ($currentUser['rol'] === 'admin' || $creador == $currentUser['id']) {
            $rol_f = ($currentUser['rol'] === 'profesor') ? 'alumno' : $_POST['rol'];
            if (!empty($_POST['password'])) {
                $pdo->prepare("UPDATE usuarios SET usuario=?, nombre=?, email=?, rol=?, password=? WHERE id=?")->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_f, password_hash($_POST['password'], PASSWORD_DEFAULT), $id_e]);
            } else {
                $pdo->prepare("UPDATE usuarios SET usuario=?, nombre=?, email=?, rol=? WHERE id=?")->execute([$_POST['usuario'], $_POST['nombre'], $_POST['email'], $rol_f, $id_e]);
            }
        }
        if ($currentUser['rol'] === 'admin') {
            $pdo->prepare("DELETE FROM clase_usuario WHERE usuario_id=?")->execute([$id_e]);
            if (!empty($_POST['clases'])) { foreach ($_POST['clases'] as $cid) $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)")->execute([$cid, $id_e]); }
        }
        $pdo->commit(); $mensaje = "Cambios guardados.";
    } catch (Exception $e) { $pdo->rollBack(); $error = $e->getMessage(); }
}

// 4. LISTADO CON SQL DE ORDENACIÓN ROBUSTO
$sql = "SELECT u.id, u.usuario, u.nombre, u.email, u.rol, u.creador_id, 
        (SELECT GROUP_CONCAT(c.nombre SEPARATOR ', ') FROM clases c JOIN clase_usuario cu ON c.id = cu.clase_id WHERE cu.usuario_id = u.id) as clase_nombre 
        FROM usuarios u WHERE u.activo = 1 ORDER BY $order_by $dir";
$usuarios = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $k => $u) {
    $stI = $pdo->prepare("SELECT clase_id FROM clase_usuario WHERE usuario_id = ?");
    $stI->execute([$u['id']]); $usuarios[$k]['clases_ids'] = $stI->fetchAll(PDO::FETCH_COLUMN);
}
$all_clases = $pdo->query("SELECT * FROM clases ORDER BY nombre ASC")->fetchAll();
require 'views/admin_users.view.php';