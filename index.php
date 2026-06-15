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
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $apiParts = array_values(array_slice($segments, 1));
    if (isset($apiParts[0]) && preg_match('/^v\d+$/i', $apiParts[0])) {
        array_shift($apiParts);
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $resource = strtolower($apiParts[0] ?? '');
    $second = strtolower($apiParts[1] ?? '');
    $third = strtolower($apiParts[2] ?? '');

    $controllerName = null;
    $action = null;
    $params = [];

    $loadController = static function (string $name) {
        $controllerFile = 'app/controllers/' . $name . '.php';
        if (!file_exists($controllerFile)) {
            return [null, null];
        }

        require_once $controllerFile;
        return [$controllerFile, $name];
    };

    $dispatchMap = [
        'products' => 'ProductApiController',
        'product' => 'ProductApiController',
        'categories' => 'CategoryApiController',
        'category' => 'CategoryApiController',
        'cart' => 'CartApiController',
        'orders' => 'OrderApiController',
        'order' => 'OrderApiController',
        'payments' => 'PaymentsApiController',
        'payment' => 'PaymentsApiController',
        'users' => 'UserApiController',
        'user' => 'UserApiController',
        'dashboard' => 'DashboardApiController',
        'home' => 'HomeApiController',
        'auth' => 'AuthApiController',
        'profile' => 'ProfileApiController',
    ];

    if (in_array($resource, ['register', 'login', 'logout', 'forgot-password', 'reset-password', 'verify-email', 'resend-verification'], true)) {
        $controllerName = 'AuthApiController';
        $action = str_replace('-', '', lcfirst(ucwords($resource, '-')));
    } elseif ($resource === 'me' || $resource === 'change-password' || $resource === 'profile') {
        $controllerName = 'ProfileApiController';
        if ($resource === 'profile') {
            $action = $method === 'GET' ? 'index' : 'update';
        } elseif ($resource === 'change-password') {
            $action = 'changePassword';
        } elseif ($resource === 'me') {
            $controllerName = 'AuthApiController';
            $action = 'me';
        }
    } elseif (isset($dispatchMap[$resource])) {
        $controllerName = $dispatchMap[$resource];
    }

    if ($controllerName === null) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Controller not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    [$controllerFile, $controllerClass] = $loadController($controllerName);
    if ($controllerFile === null || !class_exists($controllerClass)) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Controller not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $controller = new $controllerClass();

    switch ($controllerName) {
        case 'ProductApiController':
            if (in_array($second, ['search', 'filter', 'sort'], true)) {
                $action = $second;
            } elseif ($method === 'GET' && $second !== '') {
                $action = 'show';
                $params = [(int) $second];
            } elseif ($method === 'POST' && $second === '') {
                $action = 'store';
            } elseif (in_array($method, ['PUT', 'PATCH'], true) && $second !== '') {
                $action = 'update';
                $params = [(int) $second];
            } elseif ($method === 'DELETE' && $second !== '') {
                $action = 'destroy';
                $params = [(int) $second];
            } else {
                $action = 'index';
            }
            break;

        case 'CategoryApiController':
            if ($method === 'GET' && $second !== '') {
                $action = 'show';
                $params = [(int) $second];
            } elseif ($method === 'POST' && $second === '') {
                $action = 'store';
            } elseif (in_array($method, ['PUT', 'PATCH'], true) && $second !== '') {
                $action = 'update';
                $params = [(int) $second];
            } elseif ($method === 'DELETE' && $second !== '') {
                $action = 'destroy';
                $params = [(int) $second];
            } else {
                $action = 'index';
            }
            break;

        case 'CartApiController':
            if ($second === 'add' && $method === 'POST') {
                $action = 'add';
            } elseif ($second === 'update' && in_array($method, ['PUT', 'PATCH'], true)) {
                $action = 'update';
            } elseif ($second === 'clear' && $method === 'DELETE') {
                $action = 'clear';
            } elseif ($second === 'total' && $method === 'GET') {
                $action = 'total';
            } elseif ($second !== '' && $method === 'DELETE') {
                $action = 'destroy';
                $params = [(int) $second];
            } elseif ($method === 'GET') {
                $action = 'index';
            } elseif ($method === 'POST') {
                $action = 'add';
            } else {
                $action = 'index';
            }
            break;

        case 'OrderApiController':
            if ($method === 'POST' && $second === '') {
                $action = 'store';
            } elseif ($method === 'GET' && $second !== '' && $third === '') {
                $action = 'show';
                $params = [(int) $second];
            } elseif ($method === 'PUT' && $second !== '' && $third === 'cancel') {
                $action = 'cancel';
                $params = [(int) $second];
            } elseif (in_array($method, ['PUT', 'PATCH'], true) && $second !== '' && $third === 'status') {
                $action = 'status';
                $params = [(int) $second];
            } elseif ($method === 'DELETE' && $second !== '') {
                $action = 'destroy';
                $params = [(int) $second];
            } else {
                $action = 'index';
            }
            break;

        case 'PaymentsApiController':
            if ($method === 'POST' && $second === '') {
                $action = 'store';
            } elseif (in_array($method, ['PUT', 'PATCH'], true) && $second !== '' && $third === 'status') {
                $action = 'status';
                $params = [(int) $second];
            } else {
                $action = null;
            }
            break;

        case 'UserApiController':
            if ($method === 'GET' && $second !== '') {
                $action = 'show';
                $params = [(int) $second];
            } elseif ($method === 'POST' && $second === '') {
                $action = 'store';
            } elseif (in_array($method, ['PUT', 'PATCH'], true) && $second !== '') {
                $action = 'update';
                $params = [(int) $second];
            } elseif ($method === 'DELETE' && $second !== '') {
                $action = 'destroy';
                $params = [(int) $second];
            } else {
                $action = 'index';
            }
            break;

        case 'AuthApiController':
            if ($action === null) {
                if ($resource === 'auth') {
                    $authAliases = [
                        'register' => 'register',
                        'login' => 'login',
                        'logout' => 'logout',
                        'forgotpassword' => 'forgotPassword',
                        'resetpassword' => 'resetPassword',
                        'verifyemail' => 'verifyEmail',
                        'resendverification' => 'resendVerification',
                    ];
                    $action = $authAliases[$second] ?? ($second !== '' ? str_replace('-', '', lcfirst(ucwords($second, '-'))) : 'index');
                }
                if ($action === null || $action === '') {
                    $action = $resource !== 'auth'
                        ? str_replace('-', '', lcfirst(ucwords($resource, '-')))
                        : 'index';
                }
            }
            break;

        case 'ProfileApiController':
            if ($resource === 'profile') {
                $action = $method === 'GET' ? 'index' : 'update';
            } elseif ($resource === 'change-password') {
                $action = 'changePassword';
            } elseif ($resource === 'orders') {
                $action = 'orders';
            } else {
                $action = $second !== '' ? $second : 'index';
            }
            break;

        default:
            if ($method === 'GET') {
                $action = $second !== '' ? 'show' : 'index';
                if ($second !== '') {
                    $params = [(int) $second];
                }
            } elseif ($method === 'POST') {
                $action = 'store';
            } elseif (in_array($method, ['PUT', 'PATCH'], true)) {
                $action = $second !== '' ? 'update' : null;
                if ($second !== '') {
                    $params = [(int) $second];
                }
            } elseif ($method === 'DELETE') {
                $action = $second !== '' ? 'destroy' : null;
                if ($second !== '') {
                    $params = [(int) $second];
                }
            }
            break;
    }

    if ($action === null || !method_exists($controller, $action)) {
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
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
