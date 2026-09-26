<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

// Autoload para HelpDesk\*
spl_autoload_register(function ($class) {
    $prefix = 'HelpDesk\\';
    $baseDir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use HelpDesk\Core\Auth;
use HelpDesk\Core\Router;

Auth::start();

// Servir arquivos de upload de forma segura caso requisitado via /uploads/{file}
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (str_starts_with($requestUri, '/uploads/')) {
    $fileName = basename(parse_url($requestUri, PHP_URL_PATH));
    $filePath = __DIR__ . '/../storage/uploads/' . $fileName;

    if (file_exists($filePath) && is_file($filePath)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        header("Content-Type: {$mime}");
        header("Content-Length: " . filesize($filePath));
        header("Content-Disposition: inline; filename=\"{$fileName}\"");
        readfile($filePath);
        exit;
    }
    http_response_code(404);
    die("Arquivo não encontrado.");
}

$router = new Router();

// Rotas de Autenticação
$router->get('/', 'TicketController@index');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Rotas de Chamados
$router->get('/tickets', 'TicketController@index');
$router->get('/tickets/create', 'TicketController@create');
$router->post('/tickets', 'TicketController@store');
$router->get('/tickets/{id}', 'TicketController@show');
$router->post('/tickets/{id}/reply', 'TicketController@reply');
$router->post('/tickets/{id}/status', 'TicketController@updateStatus');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($requestUri, $requestMethod);
