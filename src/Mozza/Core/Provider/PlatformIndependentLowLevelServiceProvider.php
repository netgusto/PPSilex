<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Mozza\Core\Services as MozzaServices,
    Mozza\Core\Twig\MozzaExtension as TwigMozzaExtension;

class PlatformIndependentLowLevelServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {

        ###############################################################################
        # Low level services
        ###############################################################################

        # Allows to use service name as controller in route definition
        $app->register(new ServiceControllerServiceProvider());

        #
        # URL Generator service
        #

        $app->register(new UrlGeneratorServiceProvider());

        #
        # URL absolutizer service
        #

        $app['url.absolutizer'] = $app->share(function() use ($app) {
            return new MozzaServices\URLAbsolutizerService(
                $app['environment']->getSiteurl(),
                $app['environment']->getWebDir()
            );
        });

        #
        # Culture Service
        #

        $app['culture'] = $app->share(function() use ($app) {
            return new MozzaServices\CultureService(
                $app['config.site']->getCulturelocale(),
                $app['config.site']->getCulturedateformat(),
                $app['config.site']->getCulturedatetimezone()
            );
        });

        #
        # Templating Service
        #

        $app->register(new TwigServiceProvider(), array(
            'twig.options' => array(
                'cache' => $app['rootdir'] . '/app/cache/twig',
                'strict_variables' => TRUE,
                'autoescape' => TRUE,
                'debug' => $app['debug'],
            ),
        ));

        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            
            $twig->addGlobal('site', $app['config.site']);

            $twig->addExtension(new \Twig_Extensions_Extension_Text($app));

            $twig->addExtension(
                new TwigMozzaExtension(
                    $app['post.repository'],
                    $app['post.serializer'],
                    $app['url_generator'],
                    $app['post.urlgenerator'],
                    $app['markdown.processor'],
                    $app['resource.resolver'],
                    $app['post.resource.resolver'],
                    $app['url.absolutizer'],
                    $app['environment']->getDomain(),
                    $app['culture'],
                    $app['config.site']
                )
            );

            # Setting the theme namespace
            $app['twig.loader.filesystem']->addPath($app['environment']->getThemesDir() . '/' . $app['config.site']->getTheme() . '/views', 'MozzaTheme');
            $app['twig.loader.filesystem']->addPath($app['environment']->getAppDir() . '/customhtml', 'Custom');

            return $twig;
        }));

        # Enabling debug (needs twig, so immediately after twig)

        if($app['debug']) {
            
            $app->register(new WebProfilerServiceProvider(), array(
                'profiler.cache_dir' => $app['rootdir'] . '/app/cache/profiler',
            ));

        } else {
            # If debug mode is disabled, we handle the error messages nicely
            $app->error(function (\Exception $e, $code) use ($app) {

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
            });
        }
    }

    public function boot(Application $app) {
    }
}