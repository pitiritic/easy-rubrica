<?php
// controllers/install.php

require_once 'config/db.php';

$error = "";
$mensaje = "";

// Bloqueo de seguridad: si ya existe un admin, redirigir al login
try {
    $checkAdmin = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'")->fetchColumn();
    if ($checkAdmin > 0 && !isset($migracion_silenciosa)) {
        header("Location: index.php?action=login");
        exit;
    }
} catch (Exception $e) { }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_nom          = trim($_POST['admin_nombre'] ?? '');
    $admin_user         = trim($_POST['admin_user'] ?? '');
    $admin_email        = trim($_POST['admin_email'] ?? '');
    $admin_pass         = $_POST['admin_pass'] ?? '';
    $admin_pass_confirm = $_POST['admin_pass_confirm'] ?? '';

    if (empty($admin_nom) || empty($admin_user) || empty($admin_pass)) {
        $error = "Nombre, Usuario y Contraseña son obligatorios.";
    } elseif ($admin_pass !== $admin_pass_confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            installDB($pdo);

            $pass_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, nombre, email, rol) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->execute([$admin_user, $pass_hash, $admin_nom, $admin_email]);

            $mensaje = "Instalación completada.";
            header("refresh:2;url=index.php?action=login");
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

/**
 * CREACIÓN INICIAL DE TABLAS
 */
function installDB($pdo) {
    $sql = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nombre VARCHAR(100),
        email VARCHAR(100),
        rol ENUM('admin', 'profesor', 'alumno') DEFAULT 'alumno'
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS ajustes (
        clave VARCHAR(50) PRIMARY KEY,
        valor TEXT
    ) ENGINE=InnoDB;

    INSERT IGNORE INTO ajustes (clave, valor) VALUES ('db_version', '1');
    ";
    $pdo->exec($sql);
}

/**
 * MIGRACIÓN SILENCIOSA TRAS ACTUALIZACIONES
 */
function actualizarEsquemaSilencioso($pdo, $v_antigua, $v_nueva) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ajustes (clave VARCHAR(50) PRIMARY KEY, valor TEXT) ENGINE=InnoDB");

        // Ejemplo de nueva tabla tras un Docker Pull (Versión 2)
        if ($v_antigua < 2) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS competencias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB;");
        }

        // Actualizar registro de versión
        $stmt = $pdo->prepare("INSERT INTO ajustes (clave, valor) VALUES ('db_version', ?) 
                               ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$v_nueva, $v_nueva]);

    } catch (Exception $e) {
        error_log("Error en actualización silenciosa: " . $e->getMessage());
    }
}

// Solo cargar la vista si no es una llamada silenciosa desde index.php
if (!isset($v_db)) {
    require 'views/install.view.php';
}