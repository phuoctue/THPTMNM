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

if (!empty($segments) && $segments[0] === 'api') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $apiOffset = 1;
    if (isset($segments[1]) && preg_match('/^v\d+$/i', $segments[1])) {
        $apiOffset = 2;
    }

    $resource = strtolower($segments[$apiOffset] ?? '');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    $resourceMap = [
        'product' => 'ProductApiController',
        'products' => 'ProductApiController',
        'category' => 'CategoryApiController',
        'categories' => 'CategoryApiController',
        'cart' => 'CartApiController',
        'order' => 'OrderApiController',
        'orders' => 'OrderApiController',
        'auth' => 'AuthApiController',
        'user' => 'UserApiController',
        'users' => 'UserApiController',
        'profile' => 'ProfileApiController',
        'dashboard' => 'DashboardApiController',
        'home' => 'HomeApiController',
    ];

    if ($resource === '' || !isset($resourceMap[$resource])) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Controller not found']);
        exit;
    }

    $controllerName = $resourceMap[$resource];
    $controllerFile = 'app/controllers/' . $controllerName . '.php';
    $controllerClass = $controllerName;

    if (!file_exists($controllerFile)) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Controller not found']);
        exit;
    }

    require_once $controllerFile;

    if (!class_exists($controllerClass)) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Controller class not found']);
        exit;
    }

    $controller = new $controllerClass();
    $action = null;
    $params = [];
    $id = $segments[$apiOffset + 1] ?? null;
    $tail = $segments[$apiOffset + 2] ?? null;

    if (in_array($resource, ['auth', 'profile', 'dashboard', 'home'], true)) {
        $action = $id !== null ? $id : 'index';
        $params = array_slice($segments, $apiOffset + 2);
    } elseif ($resource === 'cart') {
        if ($id === 'clear') {
            $action = 'clear';
        } else {
            switch ($method) {
                case 'GET':
                    $action = 'index';
                    break;
                case 'POST':
                    $action = 'store';
                    break;
                case 'PUT':
                case 'PATCH':
                    $action = $id !== null ? 'update' : null;
                    break;
                case 'DELETE':
                    $action = $id !== null ? 'destroy' : 'clear';
                    break;
                default:
                    $action = null;
                    break;
            }

            if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true) && $id !== null && $id !== 'clear') {
                $params = [$id];
            }
        }
    } elseif ($resource === 'order' || $resource === 'orders') {
        switch ($method) {
            case 'GET':
                $action = $id !== null ? 'show' : 'index';
                break;
            case 'POST':
                $action = 'store';
                break;
            case 'PUT':
            case 'PATCH':
                $action = $id !== null ? 'update' : null;
                $params = $id !== null ? [$id] : [];
                break;
            case 'DELETE':
                $action = $id !== null ? 'destroy' : null;
                $params = $id !== null ? [$id] : [];
                break;
            default:
                $action = null;
                break;
        }
    } else {
        switch ($method) {
            case 'GET':
                $action = $id !== null ? 'show' : 'index';
                break;
            case 'POST':
                $action = 'store';
                break;
            case 'PUT':
            case 'PATCH':
                $action = $id !== null ? 'update' : null;
                $params = $id !== null ? [$id] : [];
                break;
            case 'DELETE':
                $action = $id !== null ? 'destroy' : null;
                $params = $id !== null ? [$id] : [];
                break;
            default:
                $action = null;
                break;
        }
    }

    if ($action === null || !method_exists($controller, $action)) {
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit;
    }

    call_user_func_array([$controller, $action], $params);
    exit;
}

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
