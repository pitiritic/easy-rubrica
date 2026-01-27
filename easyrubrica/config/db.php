<?php
// easyrubrica/config/db.php

$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'easyrubrica';
$user = getenv('DB_USER') ?: 'admin_user'; 
$pass = getenv('DB_PASS') ?: 'admin_pass'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("USE `$db` ");
    checkAndMigrate($pdo);
} catch (\PDOException $e) {
    // Error silencioso para que index.php active el instalador
}

function checkAndMigrate($pdo) {
    if (!$pdo) return;
    try {
        $stmt = $pdo->query("DESCRIBE usuarios");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Migración: reset_token
        if (!in_array('reset_token', $columns)) {
            $pdo->exec("ALTER TABLE usuarios ADD COLUMN reset_token VARCHAR(255) NULL AFTER password");
        }
        // Migración: reset_expires
        if (!in_array('reset_expires', $columns)) {
            $pdo->exec("ALTER TABLE usuarios ADD COLUMN reset_expires DATETIME NULL AFTER reset_token");
        }
        // NUEVA MIGRACIÓN: creador_id para instalaciones existentes
        if (!in_array('creador_id', $columns)) {
            $pdo->exec("ALTER TABLE usuarios ADD COLUMN creador_id INT NULL");
            
            // Asignar los huérfanos al primer admin que encuentre
            $stmtAdmin = $pdo->query("SELECT id FROM usuarios WHERE rol = 'admin' LIMIT 1");
            $adminId = $stmtAdmin->fetchColumn();
            if ($adminId) {
                $pdo->prepare("UPDATE usuarios SET creador_id = ? WHERE creador_id IS NULL")->execute([$adminId]);
            }
        }

        // CORRECCIÓN TIPO ENUM (Para evitar error 'Data truncated')
        $pdo->exec("ALTER TABLE evaluaciones MODIFY COLUMN tipo ENUM('auto', 'coeval', 'hetero') NOT NULL");

        // Tablas de ajustes (SMTP y Sistema)
        $pdo->exec("CREATE TABLE IF NOT EXISTS ajustes_smtp (
            id INT PRIMARY KEY,
            smtp_host VARCHAR(100),
            smtp_user VARCHAR(100),
            smtp_pass VARCHAR(255),
            smtp_port INT DEFAULT 587,
            smtp_secure ENUM('tls', 'ssl') DEFAULT 'tls',
            from_email VARCHAR(100),
            from_name VARCHAR(100) DEFAULT 'EasyRúbrica'
        ) ENGINE=InnoDB");
        $pdo->exec("INSERT IGNORE INTO ajustes_smtp (id, from_name) VALUES (1, 'EasyRúbrica')");

        $pdo->exec("CREATE TABLE IF NOT EXISTS ajustes_sistema (
            id INT PRIMARY KEY,
            url_ayuda VARCHAR(255) DEFAULT '#',
            url_acerca VARCHAR(255) DEFAULT '#'
        ) ENGINE=InnoDB");
        $pdo->exec("INSERT IGNORE INTO ajustes_sistema (id) VALUES (1)");

    } catch (Exception $e) {}
}

function installDB($pdo) {
    if (!$pdo) return;
    global $db;
    
    $pdo->exec("USE `$db` ");

    // Se añade creador_id directamente en la creación inicial
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nombre VARCHAR(100),
        email VARCHAR(100),
        rol ENUM('admin', 'profesor', 'alumno') DEFAULT 'alumno',
        reset_token VARCHAR(255) NULL,
        reset_expires DATETIME NULL,
        creador_id INT NULL
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS clases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        autor_id INT,
        FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS clase_usuario (
        clase_id INT,
        usuario_id INT,
        autor_id INT NULL,
        PRIMARY KEY (clase_id, usuario_id),
        FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS rubricas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        asignatura VARCHAR(100),
        competencias JSON,
        autor_id INT NULL,
        FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS criterios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rubrica_id INT,
        nombre VARCHAR(255) NOT NULL,
        FOREIGN KEY (rubrica_id) REFERENCES rubricas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS niveles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        criterio_id INT,
        valor INT NOT NULL,
        etiqueta VARCHAR(100),
        descriptor TEXT,
        FOREIGN KEY (criterio_id) REFERENCES niveles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS clase_rubrica (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clase_id INT,
        rubrica_id INT,
        titulo VARCHAR(150),
        estado ENUM('0', 'activa', 'cerrada') DEFAULT '0',
        autor_id INT NULL,
        peso_hetero INT DEFAULT 100,
        peso_coeval INT DEFAULT 0,
        peso_auto INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
        FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE,
        FOREIGN KEY (rubrica_id) REFERENCES rubricas(id) ON DELETE CASCADE,
        FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS evaluaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tarea_id INT,
        rubrica_id INT,
        evaluador_id INT,
        evaluado_id INT,
        tipo ENUM('auto', 'coeval', 'hetero') NOT NULL,
        calificacion_final DECIMAL(5,2) DEFAULT 0,
        comentarios TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (tarea_id) REFERENCES clase_rubrica(id) ON DELETE CASCADE,
        FOREIGN KEY (rubrica_id) REFERENCES rubricas(id) ON DELETE CASCADE,
        FOREIGN KEY (evaluador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (evaluado_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS puntuaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        evaluacion_id INT,
        criterio_id INT,
        nivel_id INT NULL,
        valor_obtenido INT, 
        FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE,
        FOREIGN KEY (criterio_id) REFERENCES criterios(id) ON DELETE CASCADE,
        FOREIGN KEY (nivel_id) REFERENCES niveles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ajustes_smtp (
        id INT PRIMARY KEY,
        smtp_host VARCHAR(100),
        smtp_user VARCHAR(100),
        smtp_pass VARCHAR(255),
        smtp_port INT DEFAULT 587,
        smtp_secure ENUM('tls', 'ssl') DEFAULT 'tls',
        from_email VARCHAR(100),
        from_name VARCHAR(100) DEFAULT 'EasyRúbrica'
    ) ENGINE=InnoDB;");
    $pdo->exec("INSERT IGNORE INTO ajustes_smtp (id, from_name) VALUES (1, 'EasyRúbrica')");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ajustes_sistema (
        id INT PRIMARY KEY,
        url_ayuda VARCHAR(255) DEFAULT '#',
        url_acerca VARCHAR(255) DEFAULT '#'
    ) ENGINE=InnoDB");
    $pdo->exec("INSERT IGNORE INTO ajustes_sistema (id) VALUES (1)");
}