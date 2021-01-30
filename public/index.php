<?php

use App\Router\Router;

require '../vendor/autoload.php';
define('DEBUG_TIME', microtime(true));
define('VIEW_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates");
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
$dotenv = \Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();
// Désactiver en prod
$whoops = new \Whoops\Run();
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
$whoops->register();

if (isset($_GET['page']) && $_GET['page'] === '1') {
    $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
    $get = $_GET;
    unset($get['page']);
    $query = http_build_query($get);
    if (!empty($query)) {
        $uri = $uri . '?' . $query;
    }
    http_response_code(302);
    header('Location :' . $uri);
    exit();
}

$router = new Router($_SERVER['REQUEST_URI']);

$router
    ->get('/', 'HomeController#index', 'index')
    ->get('/blog', 'BlogController#index', 'blog')
    ->get('/blog/[*:slug]-[i:id]', 'BlogController#show', 'show')
    ->post('/blog/[*:slug]-[i:id]', 'BlogController#show', 'show')
    ->get('/contact', 'ContactController#index', 'contact')
    ->post('/contact', 'ContactController#index', 'contact')
    ->get('/login', 'AuthentificationController#login', 'login')
    ->post('/login', 'AuthentificationController#login', 'login')
    ->post('/login/code', 'AuthentificationController#code', 'code')
    ->post('/logout', 'AuthentificationController#logout', 'logout')
    ->get('/signin', 'AuthentificationController#signIn', 'signin')
    ->post('/signin', 'AuthentificationController#signIn', 'signin')
    ->run();
