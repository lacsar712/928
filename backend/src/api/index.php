<?php
header('Content-Type: application/json; charset=utf-8');

$uri = $_SERVER['REQUEST_URI'];
$basePath = '/api/';
$scriptName = $_SERVER['SCRIPT_NAME'];

if (strpos($uri, $basePath) === false) {
    $basePath = dirname($scriptName) . '/';
}

$route = substr($uri, strlen($basePath));
$route = strtok($route, '?');
$route = trim($route, '/');

$parts = explode('/', $route);

if (count($parts) >= 2 && $parts[0] === 'weather') {
    $action = $parts[1];
    $allowed = ['today', 'forecast', 'aqi'];
    if (in_array($action, $allowed)) {
        require_once __DIR__ . '/weather.php';
        handle_weather($action);
        exit;
    }
}

http_response_code(404);
echo json_encode(['code' => 404, 'msg' => '接口不存在']);
