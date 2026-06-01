<?php
require_once 'app/libs/AuthHelper.php';
require_once 'app/models/UserModel.php';

AuthHelper::bootstrapSession();
AuthHelper::restoreRememberMe();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$segments = $url === '' ? [] : explode('/', $url);

if (!empty($segments) && $segments[0] === 'admin') {
    $resource = $segments[1] ?? 'users';
    $controllerName = ($resource === 'users') ? 'UserController' : ucfirst($resource) . 'Controller';
    $controllerFile = 'app/controllers/Admin/' . $controllerName . '.php';
    $controllerClass = 'Admin\\' . $controllerName;
    $action = $segments[2] ?? 'index';
    $params = array_slice($segments, 3);
} else {
    $controllerName = (!empty($segments[0]) && $segments[0] !== '')
        ? ucfirst($segments[0]) . 'Controller'
        : 'HomeController';
    $controllerFile = 'app/controllers/' . $controllerName . '.php';
    $controllerClass = $controllerName;
    $action = !empty($segments[1]) ? $segments[1] : 'index';
    $params = array_slice($segments, 2);
}

if (!file_exists($controllerFile)) {
    die('Controller not found: ' . htmlspecialchars($controllerFile));
}

require_once $controllerFile;

if (!class_exists($controllerClass)) {
    die('Controller class not found: ' . htmlspecialchars($controllerClass));
}

$controller = new $controllerClass();

if (!method_exists($controller, $action)) {
    die('Action not found: ' . htmlspecialchars($action));
}

call_user_func_array([$controller, $action], $params);
