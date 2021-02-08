<?php

namespace App\Controllers;

use App\Router\Router;

class HomeController extends AbstractController
{
    /**
     * Show index page
     *
     * @param Router $router The route object
     * @return void
     */
    public function index(Router $router)
    {
        return $this->twig->render(
            '/home/index.html.twig',
            [
                'router' => $router
            ]
        );
    }
}
