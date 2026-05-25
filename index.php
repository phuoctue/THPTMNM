<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Mặc định vào Product nếu không có controller
$controllerName = (isset($url[0]) && $url[0] != '')
    ? ucfirst($url[0]) . 'Controller'
    : 'HomeController';

// Mặc định action là index
$action = (isset($url[1]) && $url[1] != '') ? $url[1] : 'index';

$controllerFile = 'app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    die('Controller not found: ' . $controllerName);
}

require_once $controllerFile;
$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die('Action not found: ' . $action);
}

call_user_func_array([$controller, $action], array_slice($url, 2));
