<?php

namespace App\Helpers;

use App\Router\Router;

class CSRF
{
    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @return string|null
     */
    public static function createToken(): ?string
    {
        $token = md5(time());
        SessionHelper::sessionStart();
        $_SESSION['token'] = $token;
        return <<<HTML
        <input type="hidden" name="token" value="$token" />
HTML;
    }

    /**
     * Undocumented function
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @param string|null $token
     * @param Router $router
     * @param string $name
     * @return boolean
     */
    public static function verifToken(?string $token = null, Router $router, string $name): bool
    {
        SessionHelper::sessionStart();
        if (isset($_SESSION['token']) && $_SESSION['token'] === $token) {
            return true;
        }
        unset($_SESSION['token']);
        http_response_code(302);
        header('Location: ' . $router->generate($name) . '?accesstoken=1');
        die;
    }
}
