<?php
// easyrubrica/controllers/auth.php
require_once 'libs/Audit.php';

$error = "";

if ($action == 'logout') {
    Audit::log($pdo, "Cierre de Sesión", "El usuario cerró su sesión voluntariamente.");
    session_destroy();
    header("Location: ?action=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        Audit::log($pdo, "Inicio de Sesión", "Acceso exitoso al sistema.");
        header("Location: ?action=home"); 
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}

require 'views/login.view.php';
