<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\DoctrineServiceProvider;

use Mozza\Core\Services as MozzaServices,
    Mozza\Core\Provider\FileBasedConfigServiceProvider;

class TraditionalPlatformServiceProvider implements ServiceProviderInterface {
    
    public function register(Application $app) {
        
        ###############################################################################
        # Registering the config services
        ###############################################################################

        #
        # System config service
        #

        $app['parameters'] = array(
            'hello' => 'World !',
        );

        $app['config.system'] = $app->share(function() use ($app) {
            $provider = new FileBasedConfigServiceProvider(
                $app['environment']->getRootDir() . '/app/config/config.yml',
                $app['parameters']
            );

            return new MozzaServices\SystemConfigService(
                $provider->getAsArray()
            );
        });

        #
        # Site config service
        #

        $app['config.site'] = $app->share(function() use ($app) {
            $provider = new FileBasedConfigServiceProvider(
                $app['environment']->getDataDir() . '/config/config.yml',
                $app['parameters']
            );

            return new MozzaServices\SiteConfigService(
                $provider->getAsArray()
            );
        });

        # Debug ?
        $app['debug'] = $app['config.system']->getDebug();

        #######################################################################
        # Database connection
        #######################################################################

        $dbresolver = new MozzaServices\DatabaseUrlResolverService();

        if(($databaseurl = $app['environment']->getEnv('DATABASE_URL')) === null) {
            throw new \UnexpectedValueException('DATABASE_URL is not set in environment.');
        }

        $this->databasedsn = $dbresolver->resolve($databaseurl);

        $app->register(new DoctrineServiceProvider, array(
            'db.options' => $this->databasedsn,
        ));

        #######################################################################
        # Persistent storage
        #######################################################################

        #
        # Pathes
        #

        $app['abspath.data'] = array(
            'posts' => $app['environment']->getRootDir() . '/' . trim($app['config.system']->getPostsdir(), '/'),
            'customhtml' => $app['environment']->getRootDir() . '/app/customhtml/',
            'postsresources' => $app['environment']->getWebDir() . '/' . trim($app['config.system']->getPostswebresdir(), '/')
        );

        #
        # Resource filepath resolver
        #

        $app['resource.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\ResourceResolverService(
                $app['environment']->getWebDir(),
                $app['abspath.data']['postsresources']
            );
        });

        #
        # Post resource filepath resolver
        #

        $app['post.resource.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\PostResourceResolverService(
                $app['environment']->getWebDir(),
                $app['abspath.data']['postsresources']
            );
        });

        #
        # Post filepath resolver
        #

        $app['postfile.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\PostFileResolverService(
                $app['abspath.data']['posts'],
                $app['config.system']->getPostsExtension()
            );
        });

        #
        # Post cache handler
        #

        $app['post.cachehandler'] = $app->share(function() use ($app) {
            return new MozzaServices\PostCacheHandlerService(
                $app['system.status'],
                $app['postfile.repository'],
                $app['post.repository'],
                $app['postfile.topostconverter'],
                $app['orm.em'],
                $app['abspath.data']['posts'],
                $app['culture']
            );
        });
    }

    public function boot(Application $app) {
    }
}