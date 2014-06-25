<?php

namespace Pulpy\Admin\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\ControllerProviderInterface,
    Silex\Provider\SessionServiceProvider;

use Pulpy\Admin\Controller as AdminController;

class ControllerProvider implements ServiceProviderInterface, ControllerProviderInterface {

    public function register(Application $app) {

        # Session service (fot auth)
        $app->register(new SessionServiceProvider());

        # The view namespace
        $app['twig.loader.filesystem']->addPath($app['environment']->getSrcdir() . '/Pulpy/Admin/Resources/views', 'PulpyAdmin');

        # The controller responsible of authentication
        $app['auth.admin.controller'] = $app->share(function() use ($app) {
            return new AdminController\AuthController(
                $app['twig']
            );
        });

        # The controller responsible for the dashboard
        $app['dashboard.admin.controller'] = $app->share(function() use ($app) {
            return new AdminController\DashboardController(
                $app['twig']
            );
        });
    }

    public function connect(Application $app) {

        # creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        ###############################################################################
        # Routing the request
        ###############################################################################

        # Initialization controllers

        $controllers->get('/login', 'auth.admin.controller:loginAction')
            ->bind('admin.login');

        $controllers->get('/', 'dashboard.admin.controller:indexAction')
            ->bind('admin.home');

        return $controllers;
    }

    public function boot(Application $app) {
        $app->mount('/admin', $this->connect($app));
    }
}