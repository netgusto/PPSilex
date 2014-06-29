<?php

namespace Pulpy\Core\Provider;

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

use SilexAssetic\AsseticServiceProvider;

use Assetic;

use Pulpy\Core\Services as PulpyServices,
    Pulpy\Core\Security\UserProvider,
    Pulpy\Core\Twig\PulpyExtension as TwigPulpyExtension;

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
            return new PulpyServices\URLAbsolutizerService(
                $app['environment']->getSiteurl(),
                $app['environment']->getWebDir()
            );
        });

        #
        # Culture Service
        #

        $app['culture'] = $app->share(function() use ($app) {
            return new PulpyServices\Context\CultureService(
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
            
            $app['twig.loader.filesystem']->addPath($app['environment']->getSrcdir() . '/Pulpy/Core/Resources/views', 'PulpyCore');

            $configservice = null;
            try {
                $configservice = $app['config.site'];
            } catch(\Exception $e) {
                # Config service is not available
            }

            if(!is_null($configservice)) {

                # Config is available (not the case when initializing, for instance)
                
                $twig->addGlobal('site', $configservice);

                $twig->addExtension(new \Twig_Extensions_Extension_Text($app));

                $twig->addExtension(
                    new TwigPulpyExtension(
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
                        $configservice
                    )
                );

                # Setting the theme namespace
                $app['twig.loader.filesystem']->addPath($app['environment']->getThemesDir() . '/' . $configservice->getTheme() . '/views', 'PulpyTheme');
                $app['twig.loader.filesystem']->addPath($app['environment']->getAppDir() . '/customhtml', 'Custom');
            }

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
                'admin_login' => array(
                    'pattern' => '^/admin/login',
                    'anonymous' => TRUE,
                ),
                'admin' => array(
                    'pattern' => '^/admin',
                    'form' => array('login_path' => '/admin/login', 'check_path' => '/admin/authcheck'),
                    'logout' => array('logout_path' => '/admin/logout'),
                    'users' => $app->share(function () use ($app) {
                        return new UserProvider($app['orm.em']);
                    }),
                ),
            )
        ));

        $app['security.role_hierarchy'] = array(
            'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH'),
        );


        #
        # Assetic
        #

        $app['assetic.path_to_cache'] = $app['environment']->getCacheDir() . '/assetic';
        $app['assetic.path_to_web'] = $app['environment']->getWebDir() . '/assets';

        $app->register(new AsseticServiceProvider(), array(
            'assetic.options' => array(
                #'debug'            => $app['debug'],
                #'auto_dump_assets' => $app['debug'],
                'debug' => FALSE,
                'auto_dump_assets' => FALSE,
            )
        ));

        $app['assetic.filter_manager'] = $app->share(
            $app->extend('assetic.filter_manager', function ($fm, $app) {
                $fm->set('lessphp', new Assetic\Filter\LessphpFilter());
                return $fm;
            })
        );

    }

    public function boot(Application $app) {
    }
}