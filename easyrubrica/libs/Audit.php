<?php
// libs/Audit.php

class Audit {
    public static function log($pdo, $evento, $detalles = "") {
        if (!isset($_SESSION['user_id'])) return;
        try {
            // Obtener nombre del usuario actual si no está en sesión
            $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $nombre = $stmt->fetchColumn() ?: 'Sistema';

            $stmt = $pdo->prepare("INSERT INTO auditoria (usuario_id, usuario_nombre, evento, detalles, ip) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $nombre,
                $evento,
                $detalles,
                $_SERVER['REMOTE_ADDR'] ?: '0.0.0.0'
            ]);
        } catch (Exception $e) {
            // Error silencioso para no interrumpir el flujo de la aplicación
        }
    }
}