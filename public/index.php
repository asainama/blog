<?php

use App\Config\DotEnv;
use App\Helpers\GlobalHelper;
use App\Router\Router;

define('DEBUG_TIME', microtime(true));
define('VIEW_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates");
define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
require '../vendor/autoload.php';
(new DotEnv(ROOT . '.env'))->load();
// Désactiver en prod
if (getenv('APP_ENV') === 'dev') {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}
if (GlobalHelper::get('page') === '1') {
    $uri = explode('?', GlobalHelper::serverMethod('RESQUEST_URI'))[0];
    $get = GlobalHelper::allGet();
    unset($get['page']);
    $query = http_build_query($get);
    if (!empty($query)) {
        $uri = $uri . '?' . $query;
    }
    http_response_code(302);
    header('Location :' . $uri);
    die;
}

$router = new Router(GlobalHelper::serverMethod('RESQUEST_URI'));

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
    // Admin
    ->get('/admin', 'AdminController#postindex', 'admin_posts')
    ->get('/admin/post/[i:id]', 'AdminController#postEdit', 'admin_post_edit')
    ->post('/admin/post/[i:id]', 'AdminController#postEdit', 'admin_post_edit')
    ->get('/admin/post/new', 'AdminController#postNew', 'admin_post_new')
    ->post('/admin/post/new', 'AdminController#postNew', 'admin_post_new')
    ->post('/admin/post/delete/[i:id]', 'AdminController#postDelete', 'admin_post_delete')
    ->get('/admin/post/comments/[i:id]', 'AdminController#postComments', 'admin_post_comments')
    ->post('/admin/post/comments/[i:id]', 'AdminController#postComments', 'admin_post_comments')
    ->run();
