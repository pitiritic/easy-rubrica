<?php
// controllers/usuarios.php

// Solo admin y profesor pueden entrar aquí
if ($currentUser['rol'] !== 'admin' && $currentUser['rol'] !== 'profesor') {
    header("Location: index.php");
    exit;
}

$mensaje = "";
$error = "";

// LÓGICA DE IMPORTACIÓN MASIVA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_usuarios'])) {
    $file = $_FILES['csv_usuarios']['tmp_name'];
    $clase_id = $_POST['clase_id'];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $importados = 0;
        try {
            $pdo->beginTransaction();
            
            // Omitir cabecera si existe (opcional)
            // fgetcsv($handle); 

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if(count($data) < 3) continue; // Saltar líneas mal formadas

                $nombre = trim($data[0]);
                $email = trim($data[1]);
                $pass = password_hash(trim($data[2]), PASSWORD_DEFAULT);
                $rol = 'alumno';

                // 1. Insertar en tabla usuarios
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $email, $pass, $rol]);
                $nuevo_id = $pdo->lastInsertId();

                // 2. Vincular con la clase seleccionada
                $stmtV = $pdo->prepare("INSERT INTO clase_usuario (clase_id, usuario_id) VALUES (?, ?)");
                $stmtV->execute([$clase_id, $nuevo_id]);

                $importados++;
            }
            $pdo->commit();
            $mensaje = "Se han importado $importados alumnos correctamente.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
        fclose($handle);
    }
}

// Obtener las clases para el desplegable (Si es profesor, solo sus clases)
if ($currentUser['rol'] === 'admin') {
    $clases = $pdo->query("SELECT * FROM clases ORDER BY nombre ASC")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM clases WHERE autor_id = ? ORDER BY nombre ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $clases = $stmt->fetchAll();
}

require 'views/usuarios.view.php';
