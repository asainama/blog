<?php

namespace App\Helpers;

abstract class GlobalHelper
{
    public static function method()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    public static function serverMethod($name)
    {
        return filter_input(INPUT_SERVER, $name);
    }

    public static function get(string $name)
    {
        return filter_input(INPUT_GET, $name);
    }
    
    public static function post(string $name)
    {
        return filter_input(INPUT_POST, $name);
    }

    public static function allPost(): ?array
    {
        return filter_input_array(INPUT_POST);
    }

    public static function allGet(): ?array
    {
        return filter_input_array(INPUT_GET);
    }
}
