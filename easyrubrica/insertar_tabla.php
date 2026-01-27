<?php
/**
 * Script temporal para crear la tabla de auditoría
 */

require_once 'config/db.php';

// 1. Verificar conexión
if (!isset($pdo) || !is_object($pdo)) {
    die("Error: No se pudo establecer la conexión con la base de datos. Revisa config/db.php");
}

// 2. Definir el SQL
$sql = "CREATE TABLE IF NOT EXISTS auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT,
    usuario_nombre VARCHAR(100),
    evento VARCHAR(100),
    detalles TEXT,
    ip VARCHAR(45),
    INDEX (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    // 3. Ejecutar
    $pdo->exec($sql);
    
    echo "<div style='font-family:sans-serif; padding:20px; border:1px solid #d4edda; color:#155724; background:#d4edda; border-radius:5px;'>";
    echo "<h3>✅ ¡Éxito!</h3>";
    echo "<p>La tabla <b>auditoria</b> ha sido creada correctamente en la base de datos.</p>";
    echo "<p><b>IMPORTANTE:</b> Por seguridad, elimina este archivo (<code>setup_audit.php</code>) ahora mismo.</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family:sans-serif; padding:20px; border:1px solid #f8d7da; color:#721c24; background:#f8d7da; border-radius:5px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>No se pudo crear la tabla: " . $e->getMessage() . "</p>";
    echo "</div>";
}