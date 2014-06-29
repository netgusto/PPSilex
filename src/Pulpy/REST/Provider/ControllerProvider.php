<?php

namespace Pulpy\REST\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\ControllerProviderInterface;

use Pulpy\REST\Controller as RESTController;

class ControllerProvider implements ServiceProviderInterface, ControllerProviderInterface {

    public function register(Application $app) {

        # The REST controller for posts
        $app['post.rest.controller'] = $app->share(function() use ($app) {
            return new RESTController\PostController(
                $app['post.repository'],
                $app['post.serializer']
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

        $controllers->get('/posts', 'post.rest.controller:indexAction')
            ->bind('posts.rest.index');

        return $controllers;
    }

    public function boot(Application $app) {
        $app->mount('/api/', $this->connect($app));
    }
}