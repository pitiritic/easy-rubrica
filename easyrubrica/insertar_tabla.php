<?php
/**
 * Script de Actualización Integral de Base de Datos - EasyRúbrica
 * Ejecuta este archivo una sola vez para activar todas las nuevas funciones.
 */

require_once 'config/db.php';

// 1. Verificar conexión
if (!isset($pdo) || !is_object($pdo)) {
    die("Error: No se pudo establecer la conexión con la base de datos. Revisa config/db.php");
}

echo "<div style='font-family:sans-serif; padding:20px; max-width: 800px; margin: 0 auto;'>";
echo "<h2>🛠️ Actualizando Base de Datos...</h2>";
echo "<ul>";

try {
    // --- A. TABLA DE AUDITORÍA ---
    $sqlAudit = "CREATE TABLE IF NOT EXISTS auditoria (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        usuario_id INT,
        usuario_nombre VARCHAR(100),
        evento VARCHAR(100),
        detalles TEXT,
        ip VARCHAR(45),
        INDEX (fecha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sqlAudit);
    echo "<li>✅ Tabla <b>auditoria</b>: Verificada/Creada.</li>";

    // --- B. BORRADO LÓGICO DE USUARIOS (Soporte para históricos) ---
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN activo TINYINT(1) DEFAULT 1;");
        echo "<li>✅ Columna <b>activo</b> añadida a la tabla usuarios.</li>";
    } catch (Exception $e) {
        echo "<li>ℹ️ Columna <b>activo</b> ya existía en usuarios.</li>";
    }

    // --- C. DEPÓSITO DE TAREAS (Archivado por curso) ---
    try {
        $pdo->exec("ALTER TABLE clase_rubrica ADD COLUMN archivada TINYINT(1) DEFAULT 0;");
        echo "<li>✅ Columna <b>archivada</b> añadida a clase_rubrica.</li>";
    } catch (Exception $e) {
        echo "<li>ℹ️ Columna <b>archivada</b> ya existía en clase_rubrica.</li>";
    }

    try {
        $pdo->exec("ALTER TABLE clase_rubrica ADD COLUMN curso_academico VARCHAR(20) DEFAULT NULL;");
        echo "<li>✅ Columna <b>curso_academico</b> añadida a clase_rubrica.</li>";
    } catch (Exception $e) {
        echo "<li>ℹ️ Columna <b>curso_academico</b> ya existía en clase_rubrica.</li>";
    }

    // --- D. ANULACIÓN DE EVALUACIONES (Borrado sin permitir reintento) ---
    try {
        $pdo->exec("ALTER TABLE evaluaciones ADD COLUMN anulada TINYINT(1) DEFAULT 0;");
        echo "<li>✅ Columna <b>anulada</b> añadida a evaluaciones.</li>";
    } catch (Exception $e) {
        echo "<li>ℹ️ Columna <b>anulada</b> ya existía en evaluaciones.</li>";
    }

    echo "</ul>";
    echo "<div style='padding:20px; border:1px solid #d4edda; color:#155724; background:#d4edda; border-radius:5px; margin-top:20px;'>";
    echo "<h3>🎉 ¡Éxito total!</h3>";
    echo "<p>Todas las mejoras han sido aplicadas correctamente:</p>";
    echo "<ul>
            <li>Sistema de Auditoría de Movimientos activa.</li>
            <li>Borrado lógico de alumnos (no pierdes sus notas antiguas).</li>
            <li>Depósito de Tareas por curso académico habilitado.</li>
            <li>Gestión avanzada de borrado/anulación de evaluaciones.</li>
          </ul>";
    echo "<p><b>⚠️ IMPORTANTE:</b> Por seguridad, <b>elimina este archivo</b> (<code>insertar_tabla.php</code>) del servidor inmediatamente.</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "</ul>";
    echo "<div style='padding:20px; border:1px solid #f8d7da; color:#721c24; background:#f8d7da; border-radius:5px;'>";
    echo "<h3>❌ Error detectado</h3>";
    echo "<p>No se pudo completar la actualización: " . $e->getMessage() . "</p>";
    echo "</div>";
}
echo "</div>";