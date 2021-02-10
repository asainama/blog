<?php

namespace App\Helpers;

use App\Router\Router;
use App\Exception\AccessDeniedException;
use SessionHandlerInterface;

class Auth
{
    /**
     * Return true if session auth exists
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @return boolean
     */
    public static function isConnect()
    {
        SessionHelper::sessionStart();
        if (!isset($_SESSION['auth'])) {
            throw new AccessDeniedException("Accès non autorisé", 403);
        }
    }

    /**
     * Return true if session auth exists and role equals 1
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @return boolean
     */
    public static function isAdmin()
    {
        SessionHelper::sessionStart();
        if (isset($_SESSION['auth'])) {
            $auth = json_decode($_SESSION['auth'], true);
            if ($auth['role'] === 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Disconnect auth
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @param Router $router
     * @return void
     */
    public static function disconnect(Router $router)
    {
        try {
            self::isConnect();
        } catch (AccessDeniedException $e) {
            header('Location: ' . $router->generate('login') . '?denied=2');
            die;
        }
        unset($_SESSION['auth']);
        header('Location: ' . $router->generate('login') . '?denied=0');
    }
}
