<?php

namespace App\Helpers;

class Url
{
    /**
     * Parse int $_GET['name'] variable or return null
     * @param string   $name    name get variable
     * @param int|null $default default parameter
     * @return int|null
     */
    public static function getInt(string $name, ?int $default = null): ?int
    {
        if (!isset($_GET[$name])) {
            return $default;
        }
        if ($_GET[$name] === '0') {
            return 0;
        }
        if (!filter_var($_GET[$name], FILTER_VALIDATE_INT)) {
            throw new \Exception("Le paramètre $name n'est pas un entier");
        }
        return (int)$_GET[$name];
    }
    /**
     * Return postive int or null
     * @param string   $name    name get variable
     * @param int|null $default default parameter
     * @return int|null
     */
    public static function getPositiveInt(string $name, ?int $default = null): ?int
    {
        $param = self::getInt($name, $default);
        if ($param !== null && $param <= 0) {
            throw new \Exception("Le paramètre $name dans l'url n'est pas un entier positif");
        }
        return $param;
    }
}
