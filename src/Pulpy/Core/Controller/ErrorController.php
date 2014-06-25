<?php

namespace Pulpy\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Twig_Environment;

class ErrorController {

    protected $twig;

    public function __construct(Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function notFoundAction(Request $request, Application $app, \Exception $e, $code) {
        return $this->twig->render('@PulpyTheme/Error/error.notfound.html.twig');
    }

    public function errorAction(Request $request, Application $app, \Exception $e, $code) {
        return $this->twig->render('@PulpyTheme/Error/error.generic.html.twig');
    }
}