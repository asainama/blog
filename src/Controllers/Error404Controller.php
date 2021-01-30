<?php

namespace App\Controllers;

use App\Router\Router;

class Error404Controller extends AbstractController
{
    /**
     * Show error 404 page
     *
     * @param Router $router The route object
     * @return void
     */
    public function index(Router $router)
    {
        http_response_code(404);
        echo $this->twig->render(
            '/error/404.html.twig',
            [
            'router' => $router
            ]
        );
    }
}
