<?php

namespace App\Router;

use ReflectionClass;
use App\Exception\AccessDeniedException;

class Route
{
    public $name;
    public $path;
    public $action;
    private $params = [];
    private $query;

    public function __construct($path, $action, $name)
    {
        $this->path = trim($path, '/');
        $this->action = $action;
        $this->name = $name;
    }
    public function matches(string $url, string $regex)
    {
        if (str_contains($url, '?')) {
            $this->query = explode("?", $url);
            $url = $this->query[0];
        }
        $match = preg_match($regex, $url, $params) === 1;
        if ($match) {
            if ($params) {
                foreach ($params as $key => $value) {
                    if (is_numeric($key)) {
                        unset($params[$key]);
                    }
                }
                $this->addParams($params);
                return true;
            } else {
                return false;
            }
        }
    }
    public function getTruePath(): string
    {
        $regextypes = "#(|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)#";
        if (preg_match($regextypes, $this->path, $match)) {
            if (!empty($match)) {
                $this->path = str_replace("-", "", $this->path);
            }
        }
        $path = preg_replace($regextypes, '', $this->path);
        $regex = "#^(.)+\/*#";
        if (preg_match($regex, $path, $matches)) {
            if (empty($matches)) {
                $this->setPath("/");
            } else {
                $this->setPath("$matches[0]");
            }
        }
        return $this->path;
    }
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }
    public function addParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function execute(Router $router)
    {
        if (str_contains($this->getName(), 'admin')) {
            try {
                \App\Helpers\Auth::isConnect();
                if (!\App\Helpers\Auth::isAdmin()) {
                    header('Location: ' . $router->generate('login') . '?denied=1');
                    exit();
                }
            } catch (AccessDeniedException $e) {
                header('Location: ' . $router->generate('login') . '?denied=1');
                exit();
            }
        }
        $actions = explode('#', $this->action);
        $controller =  '\App\Controllers\\' . $actions[0];
        $method = $actions[1];
        if (class_exists($controller)) {
            $class = new ReflectionClass($controller);
            if ($class->hasMethod($method)) {
                call_user_func([new $controller(), $method], $router, $this->params);
            }
        }
    }
}
