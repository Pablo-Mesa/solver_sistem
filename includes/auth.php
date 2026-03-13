<?php
session_start();

// Si no existe la variable de sesión 'user_id', significa que no se ha logueado
if (!isset($_SESSION['user_id'])) {
    // Redirigir al login usando la URL base del proyecto (funciona desde raíz o desde subcarpetas)
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $appRoot = str_replace('\\', '/', dirname(__DIR__));
    $baseUrl  = trim(str_replace($docRoot, '', $appRoot), '/');
    $baseUrl  = $baseUrl === '' ? '' : '/' . $baseUrl;
    header('Location: ' . $baseUrl . '/login.php');
    exit();
}
?>