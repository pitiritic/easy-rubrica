<?php
// easyrubrica/controllers/ajustes.php

// Verificación de seguridad: solo administradores
if ($currentUser['rol'] != 'admin') die("Acceso denegado.");

$mensaje = "";
$error = "";

// --- 0. PROCESAR ACCIONES POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. GUARDAR CONFIGURACIÓN SMTP
    if (isset($_POST['guardar_smtp'])) {
        try {
            $stmt = $pdo->prepare("UPDATE ajustes_smtp SET 
                smtp_host = ?, smtp_user = ?, smtp_pass = ?, 
                smtp_port = ?, smtp_secure = ?, from_email = ?, from_name = ? 
                WHERE id = 1");
            $stmt->execute([
                trim($_POST['smtp_host']), trim($_POST['smtp_user']), $_POST['smtp_pass'],
                (int)$_POST['smtp_port'], $_POST['smtp_secure'],
                trim($_POST['from_email']), trim($_POST['from_name'])
            ]);
            $mensaje = "Configuración SMTP actualizada correctamente.";
        } catch (Exception $e) {
            $error = "Error al guardar SMTP: " . $e->getMessage();
        }
    }

    // B. GUARDAR AJUSTES DE SISTEMA (ENLACES DINÁMICOS)
    if (isset($_POST['guardar_sistema'])) {
        try {
            $stmt = $pdo->prepare("UPDATE ajustes_sistema SET 
                url_ayuda = ?, url_acerca = ? 
                WHERE id = 1");
            $stmt->execute([
                trim($_POST['url_ayuda']), 
                trim($_POST['url_acerca'])
            ]);
            $mensaje = "Enlaces del sistema actualizados correctamente.";
        } catch (Exception $e) {
            $error = "Error al guardar los enlaces: " . $e->getMessage();
        }
    }

    // C. PROBAR CONEXIÓN SMTP (TEST)
    if (isset($_POST['test_smtp'])) {
        require 'libs/Exception.php';
        require 'libs/PHPMailer.php';
        require 'libs/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = trim($_POST['smtp_host']);
            $mail->SMTPAuth   = true;
            $mail->Username   = trim($_POST['smtp_user']);
            $mail->Password   = $_POST['smtp_pass'];
            $mail->SMTPSecure = ($_POST['smtp_secure'] == 'ssl') ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$_POST['smtp_port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(trim($_POST['from_email']), trim($_POST['from_name']));
            $mail->addAddress(trim($_POST['smtp_user']));

            $mail->isHTML(true);
            $mail->Subject = 'Prueba de conexión - EasyRúbrica';
            $mail->Body    = "<h1>¡Éxito!</h1><p>Tu configuración SMTP en EasyRúbrica funciona correctamente.</p>";

            $mail->send();
            $mensaje = "¡Test exitoso! Correo enviado a " . $_POST['smtp_user'];
        } catch (Exception $e) {
            $error = "Fallo en el test: " . $mail->ErrorInfo;
        }
    }
}

// --- 1. GENERAR BACKUP DINÁMICO (GET) ---
if (isset($_GET['do']) && $_GET['do'] == 'backup') {
    if (ob_get_level()) ob_end_clean();
    
    $sql = "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    $stmtTables = $pdo->query("SHOW TABLES");
    $tables = $stmtTables->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $stmtCreate = $pdo->query("SHOW CREATE TABLE `$table` ");
        $rowCreate = $stmtCreate->fetch(PDO::FETCH_ASSOC);
        
        $sql .= "DROP TABLE IF EXISTS `$table`;\n" . $rowCreate['Create Table'] . ";\n\n";
        
        $stmtData = $pdo->query("SELECT * FROM `$table` ");
        while ($row = $stmtData->fetch(PDO::FETCH_ASSOC)) {
            $keys = array_map(function($k){ return "`$k`"; }, array_keys($row));
            $vals = array_map(function($v) use ($pdo){
                return $v === null ? 'NULL' : $pdo->quote($v);
            }, array_values($row));
            
            $sql .= "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ");\n";
        }
        $sql .= "\n";
    }
    
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="backup_rubrica_'.date("Y-m-d").'.sql"');
    echo $sql;
    exit;
}

// --- 2. RESTAURAR BACKUP ---
if (isset($_POST['restaurar_backup'])) {
    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == 0) {
        try {
            $sql = file_get_contents($_FILES['backup_file']['tmp_name']);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
            $pdo->exec($sql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            echo "<script>alert('Base de datos restaurada correctamente.'); window.location='?action=login';</script>";
            exit;
        } catch (PDOException $e) { $error = "Error al restaurar: " . $e->getMessage(); }
    } else { $error = "No se ha seleccionado ningún archivo."; }
}

// --- 3. RESET DE FÁBRICA ---
if (isset($_POST['reset_total'])) {
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) { $pdo->exec("DROP TABLE IF EXISTS `$table` "); }
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        session_destroy();
        header("Location: index.php?action=install");
        exit;
    } catch (PDOException $e) { $error = "Error en el reset: " . $e->getMessage(); }
}

// --- 4. CARGAR DATOS PARA LA VISTA ---
// Recuperar configuración SMTP
$smtp = $pdo->query("SELECT * FROM ajustes_smtp WHERE id = 1")->fetch();

// Recuperar configuración de enlaces del sistema
$sistema = $pdo->query("SELECT * FROM ajustes_sistema WHERE id = 1")->fetch();

/**
 * LÓGICA DE VALORES POR DEFECTO REFORZADA
 * Si la base de datos no tiene la fila, o si los campos están vacíos o contienen '#', 
 * se asignan las URLs solicitadas.
 */
if (!$sistema) {
    $sistema = ['url_ayuda' => '', 'url_acerca' => ''];
}

if (empty($sistema['url_ayuda']) || $sistema['url_ayuda'] == '#') {
    $sistema['url_ayuda'] = 'https://jmmorenas.com/easy-rubrica/ayuda-y-recursos.html';
}

if (empty($sistema['url_acerca']) || $sistema['url_acerca'] == '#') {
    $sistema['url_acerca'] = 'https://github.com/pitiritic/easy-rubrica';
}

// Cargar la vista
require 'views/ajustes.view.php';
