<?php
// CORS y headers para permitir solicitudes de otros dominios y especificar los métodos HTTP permitidos
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE');
header('Content-Type: application/json');

// Cargar controladores y modelos manualmente (autoload básico)
require_once '../Controllers/CamisetaController.php';
require_once '../Controllers/ClienteController.php';
require_once '../Models/Camiseta.php';
require_once '../Models/Cliente.php';

// Obtener método y URI de la solicitud
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Ruta base si está alojado en subcarpeta, puedes ajustar:
$base = '/todocamisetas-api/public'; // <--- cambia esto si tu app está en otro directorio

// Eliminar base de URI
if (strpos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}

// Definir las rutas
$routes = [
    // CAMISETAS
    ['GET', '~^/camisetas$~', 'CamisetaController@index'],
    ['GET', '~^/camisetas/(\d+)$~', 'CamisetaController@show'],
    ['POST', '~^/camisetas$~', 'CamisetaController@store'],
    ['PUT', '~^/camisetas/(\d+)$~', 'CamisetaController@update'],
    ['PATCH', '~^/camisetas/(\d+)$~', 'CamisetaController@patch'],
    ['DELETE', '~^/camisetas/(\d+)$~', 'CamisetaController@destroy'],
    ['GET', '~^/camisetas/(\d+)/precio/(\d+)$~', 'CamisetaController@precioFinal'],

    // CLIENTES
    ['GET', '~^/clientes$~', 'ClienteController@index'],
    ['GET', '~^/clientes/(\d+)$~', 'ClienteController@show'],
    ['POST', '~^/clientes$~', 'ClienteController@store'],
    ['PUT', '~^/clientes/(\d+)$~', 'ClienteController@update'],
    ['PATCH', '~^/clientes/(\d+)$~', 'ClienteController@patch'],
    ['DELETE', '~^/clientes/(\d+)$~', 'ClienteController@destroy'],
];

// Buscar coincidencia
$found = false;

foreach ($routes as [$routeMethod, $pattern, $handler]) {
    if ($method === $routeMethod && preg_match($pattern, $uri, $matches)) {
        $found = true;
        array_shift($matches); // Eliminar la ruta completa del match
        [$controllerName, $action] = explode('@', $handler);
        $controller = new $controllerName;
        call_user_func_array([$controller, $action], $matches);
        break;
    }
}

// Si no se encontró la ruta
if (!$found) {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
}
?>