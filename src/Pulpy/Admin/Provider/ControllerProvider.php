<?php

namespace Pulpy\Admin\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\ControllerProviderInterface,
    Silex\Provider\SessionServiceProvider;

use Assetic;

use Pulpy\Admin\Controller as AdminController;

class ControllerProvider implements ServiceProviderInterface, ControllerProviderInterface {

    public function register(Application $app) {

        # Session service (fot auth)
        $app->register(new SessionServiceProvider());

        # The view namespace
        $app['twig.loader.filesystem']->addPath($app['environment']->getSrcdir() . '/Pulpy/Admin/Resources/views', 'PulpyAdmin');

        $app['assetic.asset_manager'] = $app->share(
            $app->extend('assetic.asset_manager', function ($am, $app) {

                # stylesheet
                $am->set('styles', new Assetic\Asset\AssetCollection(
                    new Assetic\Asset\GlobAsset(
                        array(
                            $app['environment']->getSrcdir() . '/Pulpy/Admin/Resources/sources/less/*.less'
                        ),
                        array($app['assetic.filter_manager']->get('lessphp'))
                    )
                ));
                $am->get('styles')->setTargetPath('admin/styles.css');

                return $am;
            })
        );

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

        # The controller responsible for the post list
        $app['posts.admin.controller'] = $app->share(function() use ($app) {
            return new AdminController\PostsController(
                $app['twig'],
                $app['post.repository']
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

        $controllers->get('/logout')
            ->bind('admin.logout');

        $controllers->get('/', 'posts.admin.controller:indexAction')
            ->bind('admin.home');

        return $controllers;
    }

    public function boot(Application $app) {
        $app->mount('/admin', $this->connect($app));
    }
}