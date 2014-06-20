<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\ControllerProviderInterface;

use Mozza\Core\Controller as MozzaController,
    Mozza\Core\Services as MozzaServices,
    Mozza\Core\Exception as MozzaException;

class ControllerProvider implements ServiceProviderInterface, ControllerProviderInterface {
    
    public function register(Application $app) {
        # The controller responsible for the homepage
        $app['home.controller'] = $app->share(function() use ($app) {
            return new MozzaController\HomeController(
                $app['twig'],
                $app['post.repository'],
                $app['postfile.resolver'],
                $app['config.site']->getHomepostsperpage()
            );
        });

        # The controller responsible for displaying a post
        $app['post.controller'] = $app->share(function() use ($app) {
            return new MozzaController\PostController(
                $app['twig'],
                $app['post.repository'],
                $app['postfile.resolver']
            );
        });

        # The controller responsible for the RSS/Atoms feeds
        $app['feed.controller'] = $app->share(function() use ($app) {
            return new MozzaController\FeedController(
                $app['post.repository'],
                $app['post.serializer'],
                $app['post.resource.resolver'],
                $app['url.absolutizer'],
                $app['config.site']
            );
        });

        # The controller responsible for the JSON feed
        $app['json.controller'] = $app->share(function() use ($app) {
            return new MozzaController\JsonController(
                $app['post.repository'],
                $app['post.serializer']
            );
        });

        # The controller responsible for error handling
        $app['error.controller'] = $app->share(function() use ($app) {
            return new MozzaController\ErrorController(
                $app['twig']
            );
        });

        # The controller responsible for maintenance handling
        $app['error.maintenance'] = $app->share(function() use ($app) {
            return new MozzaController\MaintenanceController(
                $app['twig']
            );
        });
    }

    public function connect(Application $app) {
    }

    public function boot(Application $app) {
        ###############################################################################
        # Routing the request
        ###############################################################################

        # Filename empty: The Home Page (All Posts)
        $app->get('/', 'home.controller:indexAction')
            ->bind('home');

        # Home page with page > 1
        $app->match('/page/{page}', 'home.controller:indexAction')
            ->assert('page', '[2-9]|[0-9]{2,}')
            ->bind('home_paged');

        # Filename /rss or /atom: RSS Feed
        $app->get('rss', 'feed.controller:indexAction')
            ->bind('rss');

        # Filename /rss or /atom: RSS Feed
        $app->get('json/posts', 'json.controller:indexAction')
            ->bind('json.posts');

        # Filename /rss or /atom: RSS Feed
        $app->get('json/posts/{slug}', 'json.controller:postAction')
            ->assert('slug', '.+')
            ->bind('json.post');

        # Filename path/to/post.md: Single Post Pages
        $app->get('{slug}', 'post.controller:indexAction')
            ->assert('slug', '.+')
            ->assert('slug', '^((?!_profiler).)*$')
            ->bind('post');

        $app->error(function (\Exception $e, $code) use ($app) {

            if($e instanceof MozzaException\ApplicationNeedsMaintenanceExceptionInterface) {
                return $app['error.maintenance']->reactToExceptionAction(
                    $app['request'],
                    $app,
                    $e,
                    $code
                );
            }

            if(!$app['debug']) {
                
                # Debug is not enabled; we display a nice, error-message free informative page

                if($code === 404 || $e instanceof PostNotFoundException) {
                    return $app['error.controller']->notFoundAction(
                        $app['request'],
                        $app,
                        $e,
                        $code
                    );
                }

                return $app['error.controller']->errorAction(
                    $app['request'],
                    $app,
                    $e,
                    $code
                );
            }

        });
    }
}