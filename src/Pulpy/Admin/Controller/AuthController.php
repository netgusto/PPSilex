<?php

namespace Pulpy\Admin\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\SecurityContext,
    Twig_Environment;

class AuthController {

    protected $twig;

    public function __construct(Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function loginAction(Request $request, Application $app) {

        if($app['security']->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new \Exception("Error Processing Request", 1);
        }

        if($app['security']->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new \Exception("Error Processing Request", 1);
        }

        return $this->twig->render('@PulpyAdmin/Auth/login.html.twig', array(
            // last username entered by the user
            'last_username' => $app['session']->get('_security.last_username'),
            'error' => $app['security.last_error']($request),
        ));
    }
}