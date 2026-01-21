<?php
// easyrubrica/controllers/auth.php

$error = "";

if ($action == 'logout') {
    session_destroy();
    header("Location: ?action=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Buscar usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        
        // CAMBIO: Redirigir al Panel de Control (home) en lugar de evaluar
        header("Location: ?action=home"); 
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}

require 'views/login.view.php';
?>
