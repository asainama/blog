<?php

namespace App\Helpers;

use App\Router\Router;
use App\Exception\AccessDeniedException;

class Auth
{
    public static function isConnect()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['auth'])) {
            throw new AccessDeniedException("Accès non autorisé", 403);
        }
    }
    
    public static function isAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['auth'])) {
            $auth = json_decode($_SESSION['auth'], true);
            if ($auth['role'] === 1) {
                return true;
            }
            return false;
        }
    }

    public static function disconnect(Router $router)
    {
        try {
            self::isConnect();
        } catch (AccessDeniedException $e) {
            header('Location: ' . $router->generate('login') . '?denied=2');
            exit();
        }
        unset($_SESSION['auth']);
        // session_destroy();
        header('Location: ' . $router->generate('login') . '?denied=0');
    }
}
