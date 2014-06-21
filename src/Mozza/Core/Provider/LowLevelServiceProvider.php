<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider,
    Silex\Provider\FormServiceProvider,
    Silex\Provider\ValidatorServiceProvider,
    Silex\Provider\TranslationServiceProvider,
    Silex\Provider\SecurityServiceProvider;


use Mozza\Core\Services as MozzaServices,
    Mozza\Core\Twig\MozzaExtension as TwigMozzaExtension;

class LowLevelServiceProvider implements ServiceProviderInterface {

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
            return new MozzaServices\Context\CultureService(
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
            $app['twig.loader.filesystem']->addPath($app['environment']->getSrcdir() . '/Mozza/Core/Resources/views', 'MozzaCore');
            $app['twig.loader.filesystem']->addPath($app['environment']->getAppDir() . '/customhtml', 'Custom');

            return $twig;
        }));

        # Enabling debug (needs twig, so immediately after twig)

        /*
        if($app['debug']) {
            
            $app->register(new WebProfilerServiceProvider(), array(
                'profiler.cache_dir' => $app['rootdir'] . '/app/cache/profiler',
            ));

        }
        */

        #
        # Form services
        #

        $app->register(new FormServiceProvider());
        $app->register(new ValidatorServiceProvider());
        $app->register(new TranslationServiceProvider(), array(
            'locale_fallbacks' => array('en'),
        ));

        #
        # Security
        #

        $app->register(new SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'admin' => array(
                    'pattern' => '^/admin',
                    'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
                    'users' => array(
                        // raw password is foo
                        'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
                    ),
                ),
            )
        ));
    }

    public function boot(Application $app) {
    }
}