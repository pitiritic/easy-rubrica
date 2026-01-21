<?php
// controllers/install.php

require_once 'config/db.php';

$error = "";
$mensaje = "";

// Bloqueo de seguridad: si ya existe un admin, redirigir al login
try {
    $checkAdmin = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'")->fetchColumn();
    if ($checkAdmin > 0) {
        header("Location: index.php?action=login");
        exit;
    }
} catch (Exception $e) {
    // Si la tabla no existe, continuamos con la instalación
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_nom          = trim($_POST['admin_nombre'] ?? '');
    $admin_user         = trim($_POST['admin_user'] ?? '');
    $admin_email        = trim($_POST['admin_email'] ?? '');
    $admin_pass         = $_POST['admin_pass'] ?? '';
    $admin_pass_confirm = $_POST['admin_pass_confirm'] ?? '';

    // Validaciones básicas
    if (empty($admin_nom) || empty($admin_user) || empty($admin_pass)) {
        $error = "Nombre, Usuario y Contraseña son campos obligatorios.";
    } elseif ($admin_pass !== $admin_pass_confirm) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($admin_pass) < 4) {
        $error = "La contraseña debe tener al menos 4 caracteres.";
    } else {
        try {
            // 1. Crear Estructura de Base de Datos completa
            installDB($pdo);

            // 2. Insertar Usuario Administrador
            $pass_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, nombre, email, rol) VALUES (?, ?, ?, ?, 'admin')");
            $stmt->execute([$admin_user, $pass_hash, $admin_nom, $admin_email]);

            $mensaje = "Instalación completada con éxito. Creando tablas y usuario administrador...";
            header("refresh:2;url=index.php?action=login");
        } catch (Exception $e) {
            $error = "Error durante la instalación: " . $e->getMessage();
        }
    }
}

require 'views/install.view.php';
