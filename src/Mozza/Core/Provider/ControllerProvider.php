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
        $app['maintenance.controller'] = $app->share(function() use ($app) {
            return new MozzaController\MaintenanceController(
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

        # Maintenance controllers

        $app->get('_maintenance/welcome', 'maintenance.controller:welcomeAction')
            ->bind('_maintenance_welcome');

        $app->match('_maintenance/welcome/step1/db', 'maintenance.controller:welcomeStep1CreateDbAction')
            ->bind('_maintenance_welcome_step1_createdb')
            ->assert('_method', 'get|post');

        $app->match('_maintenance/welcome/step1/createschema', 'maintenance.controller:welcomeStep1CreateSchemaAction')
            ->bind('_maintenance_welcome_step1_createschema')
            ->assert('_method', 'get|post');

        $app->match('_maintenance/welcome/step1/updateschema', 'maintenance.controller:welcomeStep1UpdateSchemaAction')
            ->bind('_maintenance_welcome_step1_updateschema')
            ->assert('_method', 'get|post');

        $app->match('_maintenance/welcome/step2', 'maintenance.controller:welcomeStep2Action')
            ->bind('_maintenance_welcome_step2')
            ->assert('_method', 'get|post');

        $app->get('_maintenance/welcome/finish', 'maintenance.controller:welcomeFinishAction')
            ->bind('_maintenance_welcome_finish');

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

            $maintenanceexception = null;
            
            if($e instanceof MozzaException\ApplicationNeedsMaintenanceExceptionInterface) {
                $maintenanceexception = $e;
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
                                    $maintenanceexception = new MozzaException\DatabaseNeedsUpdateException();
                                    $maintenanceexception->setInformationalLabel($errorinfo['2']);
                                    break;
                                }
                            }
                            break;
                        }
                    }
                }

                if(is_null($maintenanceexception)) {
                    # Heuristic error detection

                    # We check if the database exists
                    try {
                        $tables = $app['db']->getSchemaManager()->listTableNames();
                    } catch(\PDOException $e) {
                        if(strpos($e->getMessage(), 'Access denied') !== FALSE) {
                            $maintenanceexception = new MozzaException\DatabaseInvalidCredentialsException();
                        } else {
                            $maintenanceexception = new MozzaException\DatabaseMissingException();
                        }
                    }

                    if(
                        is_null($maintenanceexception) && (
                            stripos($e->getMessage(), 'Invalid table name') !== FALSE ||
                            stripos($e->getMessage(), 'no such table') !== FALSE ||
                            stripos($e->getMessage(), 'Base table or view not found') !== FALSE
                        )
                    ) {
                        if(empty($tables)) {
                            $maintenanceexception = new MozzaException\DatabaseEmptyException();
                        } else {
                            $maintenanceexception = new MozzaException\DatabaseNeedsUpdateException();
                        }
                    }

                    if(
                        is_null($maintenanceexception) && (
                            stripos($e->getMessage(), 'Unknown column') !== FALSE
                        )
                    ) {
                        $maintenanceexception = new MozzaException\DatabaseNeedsUpdateException();
                    }
                }
            }

            if(!is_null($maintenanceexception)) {

                # Enabling the anonymous maintenance mode
                $app['environment']->setAnonymousMaintenance(TRUE);

                if(strpos($app['request']->attributes->get('_route'), '_maintenance_') === 0) {
                    
                    # maintenance in progress; just proceed with the requested controller
                    return $app['maintenance.controller']->proceedWithMaintenanceRequestAction(
                        $app['request'],
                        $app,
                        $maintenanceexception,
                        $code
                    );
                } else {
                    return $app['maintenance.controller']->reactToExceptionAction(
                        $app['request'],
                        $app,
                        $maintenanceexception,
                        $code
                    );
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