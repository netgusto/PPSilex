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

        # The controller responsible for initialization handling
        $app['initialization.controller'] = $app->share(function() use ($app) {
            return new MozzaController\InitializationController(
                $app['twig'],
                $app['environment'],
                $app['url_generator'],
                $app['form.factory'],
                $app['orm.em']
            );
        });

        # The controller responsible for user auth
        $app['auth.controller'] = $app->share(function() use ($app) {
            return new MozzaController\AuthController(
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

        # Initialization controllers

        $app->get('_init/welcome', 'initialization.controller:welcomeAction')
            ->bind('_init_welcome');

        $app->match('_init/step1/db', 'initialization.controller:step1CreateDbAction')
            ->bind('_init_step1_createdb')
            ->assert('_method', 'get|post');

        $app->match('_init/step1/createschema', 'initialization.controller:step1CreateSchemaAction')
            ->bind('_init_step1_createschema')
            ->assert('_method', 'get|post');

        $app->match('_init/step1/updateschema', 'initialization.controller:step1UpdateSchemaAction')
            ->bind('_init_step1_updateschema')
            ->assert('_method', 'get|post');

        $app->match('_init/step2', 'initialization.controller:step2Action')
            ->bind('_init_step2')
            ->assert('_method', 'get|post');

        $app->get('_init/finish', 'initialization.controller:finishAction')
            ->bind('_init_finish');

        $app->get('login', 'auth.controller:loginAction')
            ->bind('login');

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

            $refinedexception = null;
            
            if(
                $e instanceof MozzaException\MaintenanceNeeded\MaintenanceNeededExceptionInterface ||
                $e instanceof MozzaException\InitializationNeeded\InitializationNeededExceptionInterface
            ) {
                $refinedexception = $e;
            } else if(
                $e instanceof \Doctrine\DBAL\DBALException ||
                $e instanceof \PDOException
            ) {

                try {
                    $errorinfo = $app['db']->errorInfo();
                } catch(\Exception $e) {
                    # we could not fetch error info (happens with mysql, when access denied)
                    $errorinfo = null;
                }

                if(!is_null($errorinfo)) {
                    # Deterministic error detection
                    $sqlstate = $errorinfo[0];
                    $errorclass = strtoupper(substr($errorinfo[0], 0, 2));
                    $errorsubclass = strtoupper(substr($errorinfo[0], 2));
                    
                    switch($errorclass) {
                        case 'HY': {
                            # driver custom error
                            break;
                        }
                        case '42': {
                            switch($errorsubclass) {
                                case 'S22': {
                                    $refinedexception = new MozzaException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException();
                                    $refinedexception->setInformationalLabel($errorinfo['2']);
                                    break;
                                }
                            }
                            break;
                        }
                    }
                }

                if(is_null($refinedexception)) {
                    # Heuristic error detection

                    # We check if the database exists
                    try {
                        $tables = $app['db']->getSchemaManager()->listTableNames();
                    } catch(\PDOException $e) {
                        if(strpos($e->getMessage(), 'Access denied') !== FALSE) {
                            $refinedexception = new MozzaException\MaintenanceNeeded\DatabaseInvalidCredentialsMaintenanceNeededException();
                        } else {
                            $refinedexception = new MozzaException\InitializationNeeded\DatabaseMissingInitializationNeededException();
                        }
                    }

                    if(
                        is_null($refinedexception) && (
                            stripos($e->getMessage(), 'Invalid table name') !== FALSE ||
                            stripos($e->getMessage(), 'no such table') !== FALSE ||
                            stripos($e->getMessage(), 'Base table or view not found') !== FALSE
                        )
                    ) {
                        if(empty($tables)) {
                            $refinedexception = new MozzaException\InitializationNeeded\DatabaseEmptyInitializationNeededException();
                        } else {
                            $refinedexception = new MozzaException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException();
                        }
                    }

                    if(
                        is_null($refinedexception) && (
                            stripos($e->getMessage(), 'Unknown column') !== FALSE
                        )
                    ) {
                        $refinedexception = new MozzaException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException();
                    }
                }
            }

            if(!is_null($refinedexception)) {

                if($refinedexception instanceOf MozzaException\InitializationNeeded\InitializationNeededExceptionInterface) {

                    # Enabling initialization mode
                    $app['environment']->setInitializationMode(TRUE);

                    if(strpos($app['request']->attributes->get('_route'), '_init_') === 0) {
                        
                        # maintenance in progress; just proceed with the requested controller
                        return $app['initialization.controller']->proceedWithInitializationRequestAction(
                            $app['request'],
                            $app,
                            $refinedexception
                        );
                    } else {
                        return $app['initialization.controller']->reactToExceptionAction(
                            $app['request'],
                            $app,
                            $refinedexception
                        );
                    }
                } else if($refinedexception instanceOf MozzaException\MaintenanceNeeded\MaintenanceNeededExceptionInterface) {
                    # Maintenance exception are not handled yet
                    throw $refinedexception;
                }
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