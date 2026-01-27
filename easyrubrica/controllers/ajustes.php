<?php
// easyrubrica/controllers/ajustes.php
require_once 'libs/Audit.php';

if ($currentUser['rol'] != 'admin') die("Acceso denegado.");

$mensaje = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_smtp'])) {
        try {
            $stmt = $pdo->prepare("UPDATE ajustes_smtp SET smtp_host = ?, smtp_user = ?, smtp_pass = ?, smtp_port = ?, smtp_secure = ?, from_email = ?, from_name = ? WHERE id = 1");
            $stmt->execute([trim($_POST['smtp_host']), trim($_POST['smtp_user']), $_POST['smtp_pass'], (int)$_POST['smtp_port'], $_POST['smtp_secure'], trim($_POST['from_email']), trim($_POST['from_name'])]);
            Audit::log($pdo, "Ajustes SMTP", "Se actualizó la configuración del correo.");
            $mensaje = "Configuración SMTP actualizada correctamente.";
        } catch (Exception $e) { $error = "Error al guardar SMTP: " . $e->getMessage(); }
    }

    if (isset($_POST['guardar_sistema'])) {
        try {
            $stmt = $pdo->prepare("UPDATE ajustes_sistema SET url_ayuda = ?, url_acerca = ? WHERE id = 1");
            $stmt->execute([trim($_POST['url_ayuda']), trim($_POST['url_acerca'])]);
            Audit::log($pdo, "Ajustes Sistema", "Se actualizaron los enlaces de ayuda/acerca de.");
            $mensaje = "Enlaces del sistema actualizados correctamente.";
        } catch (Exception $e) { $error = "Error al guardar los enlaces: " . $e->getMessage(); }
    }
}

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
            $vals = array_map(function($v) use ($pdo){ return $v === null ? 'NULL' : $pdo->quote($v); }, array_values($row));
            $sql .= "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $vals) . ");\n";
        }
    }
    Audit::log($pdo, "Copia de Seguridad", "Se generó y descargó un respaldo de la base de datos.");
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="backup_rubrica_'.date("Y-m-d").'.sql"');
    echo $sql; exit;
}

if (isset($_POST['restaurar_backup'])) {
    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == 0) {
        try {
            $sql = file_get_contents($_FILES['backup_file']['tmp_name']);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
            $pdo->exec($sql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            Audit::log($pdo, "Restauración", "Se restauró una copia de seguridad.");
            echo "<script>alert('Base de datos restaurada correctamente.'); window.location='?action=login';</script>"; exit;
        } catch (PDOException $e) { $error = "Error al restaurar: " . $e->getMessage(); }
    }
}

$smtp = $pdo->query("SELECT * FROM ajustes_smtp WHERE id = 1")->fetch();
$sistema = $pdo->query("SELECT * FROM ajustes_sistema WHERE id = 1")->fetch();
if (!$sistema) $sistema = ['url_ayuda' => 'https://jmmorenas.com/easy-rubrica/ayuda-y-recursos.html', 'url_acerca' => 'https://github.com/pitiritic/easy-rubrica'];

require 'views/ajustes.view.php';