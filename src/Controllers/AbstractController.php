<?php

namespace App\Controllers;

abstract class AbstractController
{

    /**
     * Twig Object
     * @var Twig $twig Twig object
     */
    protected $twig;

    /**
     * Init AbstractController
     *
     * @return void
     */
    public function __construct()
    {
        $this->twig = new \Twig\Environment(
            new \Twig\Loader\FilesystemLoader(VIEW_PATH),
            [
                // 'cache'=>__DIR__.DIRECTORY_SEPARATOR.'tmp',
                'cache' => false,
                'debug' => true
            ]
        );
        $this->twig->addExtension(new \App\Twig\AppExtension());
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }
}
