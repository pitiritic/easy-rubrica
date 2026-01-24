<?php
// index.php
session_start();
require 'config/db.php'; 

// --- CARGA GLOBAL DE AJUSTES DEL SISTEMA ---
try {
    $stmtSis = $pdo->query("SELECT url_ayuda, url_acerca FROM ajustes_sistema WHERE id = 1");
    $sistema = $stmtSis->fetch(PDO::FETCH_ASSOC);

    if (!$sistema) { $sistema = ['url_ayuda' => '', 'url_acerca' => '']; }
    
    // Valores por defecto si están vacíos o tienen '#'
    if (empty($sistema['url_ayuda']) || $sistema['url_ayuda'] == '#') {
        $sistema['url_ayuda'] = 'https://jmmorenas.com/easy-rubrica/ayuda-y-recursos.html';
    }
    if (empty($sistema['url_acerca']) || $sistema['url_acerca'] == '#') {
        $sistema['url_acerca'] = 'https://github.com/pitiritic/easy-rubrica';
    }
} catch (Exception $e) {
    $sistema = ['url_ayuda' => 'https://jmmorenas.com/easy-rubrica/ayuda-y-recursos.html', 'url_acerca' => 'https://github.com/pitiritic/easy-rubrica'];
}

$currentUser = $_SESSION['user'] ?? null;
$action = $_GET['action'] ?? 'inicio'; // Si no hay acción, vamos a inicio

// Si no hay sesión, forzar login (ajusta el nombre de tu archivo de login aquí)
if (!$currentUser && !in_array($action, ['login', 'install'])) {
    // Si tu archivo de login se llama de otra forma, cambia 'login' por el nombre correcto
    $action = 'login'; 
}

$controllerPath = "controllers/" . $action . ".php";

if (file_exists($controllerPath)) {
    require $controllerPath;
} else {
    // Si el archivo no existe, no bloqueamos la pantalla. 
    // Intentamos cargar el controlador principal (ajusta el nombre si no es dashboard.php)
    if ($currentUser && file_exists("controllers/dashboard.php")) {
        require 'controllers/dashboard.php';
    } elseif ($currentUser && file_exists("controllers/inicio.php")) {
        require 'controllers/inicio.php';
    } else {
        echo "Cargando sistema..."; // Fallback visual
    }
}