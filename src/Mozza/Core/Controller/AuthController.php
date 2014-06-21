<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Twig_Environment;

class AuthController {

    public function __construct(Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function loginAction(Request $request, Application $app) {
        return 'Hello, World !';
    }
}