<?php

namespace App\Helpers;

use App\Router\Router;

class CSRF
{
    public static function createToken(): ?string
    {
        $token = md5(time());
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['token'] = $token;
        return <<<HTML
        <input type="hidden" name="token" value="$token" />
HTML;
    }

    public static function verifToken(?string $token = null, Router $router, string $name): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['token']) && $_SESSION['token'] === $token) {
            return true;
        }
        unset($_SESSION['token']);
        http_response_code(302);
        header('Location: ' . $router->generate($name) . '?accesstoken=1');
        die();
    }
}
