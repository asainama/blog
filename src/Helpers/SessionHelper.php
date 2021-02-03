<?php

namespace App\Helpers;

class SessionHelper
{
    public static function sessionStart()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(0, '/', 'localhost', false, true);
            session_start();
            session_regenerate_id(true);
        }
    }
}
