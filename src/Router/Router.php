<?php

namespace App\Router;

class Router
{
    private $url;

    public $routes = [];

    private $matchTypes = [
        'i'  => '[0-9]++',
        'a'  => '[0-9A-Za-z]++',
        'h'  => '[0-9A-Fa-f]++',
        '*'  => '.+?',
        '**' => '.++',
        ''   => '[^/\.]++'
    ];

    public function __construct($url, array $matchTypes = [])
    {
        $this->url = trim($url, '/');
        $this->addMatchTypes($matchTypes);
    }

    public function addMatchTypes(array $matchTypes)
    {
        $this->matchTypes = array_merge($this->matchTypes, $matchTypes);
    }
    public function get(string $path, string $action, string $name): self
    {
        $this->routes['GET'][] = new Route($path, $action, $name);
        return $this;
    }
    public function post(string $path, string $action, string $name): self
    {
        $this->routes['POST'][] = new Route($path, $action, $name);
        return $this;
    }
    public function match(string $path, string $action, string $name): self
    {
        $this->routes['GET|POST'][] = new Route($path, $action, $name);
        return $this;
    }

    public function run()
    {
        $column = array_column($this->routes[$_SERVER['REQUEST_METHOD']], 'path');
        $url = $this->url;
        if (str_contains($url, '?')) {
            $this->query = explode("?", $url);
            $url = $this->query[0];
        }
        $validPath = array_filter($column, function ($path) use ($url) {
            $regex = $this->compileRoute($path);
            $ok = preg_match($regex, $url, $match);
            return $ok === 1 ? $path : false;
        });
        if (!empty($validPath) || $url === "") {
            foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
                $regex = $this->compileRoute($route->path);
                if ($route->matches($this->url, $regex)) {
                    $route->execute($this);
                }
            }
        } else {
            print_r(call_user_func([new \App\Controllers\Error404Controller(), "index"], $this));
        }
    }

    public function generate(string $name, array $params = [], $action = 'GET')
    {
        $route = $this->routes;
        $column = array_column($route[$action], 'name');
        $key = array_search($name, $column);
        $path = '/';
        if ($key !== false) {
            $path .= $route[$action][$key]->getTruePath();
            if (!empty($params)) {
                if (array_key_exists("id", $params) && (array_key_exists("slug", $params))) {
                    $id = $params['id'];
                    $slug = $params['slug'];
                    $path .= "$slug-$id";
                } elseif (array_key_exists("id", $params)) {
                    $id = $params['id'];
                    $path .= "$id";
                }
                $route[$action][$key]->addParams($params);
            }
            return "http://$_SERVER[HTTP_HOST]" . $path;
        } else {
            // throw new RuntimeException("Route '{$name}' does not exist.");
        }
    }

    protected function compileRoute($path)
    {
        // var_dump($route->path);
        // $path = $route->path;
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $path, $this->matches, PREG_SET_ORDER)) {
            $matchTypes = $this->matchTypes;
            foreach ($this->matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;
                if (isset($matchTypes[$type])) {
                    $type = $matchTypes[$type];
                }
                if ($pre === '.') {
                    $pre = '\.';
                }

                $optional = $optional !== '' ? '?' : null;

                //Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                        . ($pre !== '' ? $pre : null)
                        . '('
                        . ($param !== '' ? "?P<$param>" : null)
                        . $type
                        . ')'
                        . $optional
                        . ')'
                        . $optional;

                $path = str_replace($block, $pattern, $path);
            }
        }
        return "`^$path$`u";
    }
}
