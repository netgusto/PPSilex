<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\DoctrineServiceProvider;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

use Mozza\Core\Services as MozzaServices;

class ClassicPlatformServiceProvider implements ServiceProviderInterface {
    
    public function register(Application $app) {
        
        ###############################################################################
        # Registering the config services
        ###############################################################################

        #
        # System config service
        #

        $parameters = array(
            'hello' => 'World !',
        );

        $filebackedconfig = new MozzaServices\FileBackedConfigLoaderService($parameters);

        $app['config.system'] = $app->share(function() use ($app, $filebackedconfig) {
            return new MozzaServices\SystemConfigService(
                $filebackedconfig->load($app['environment']->getRootDir() . '/app/config/config.yml')
            );
        });

        # Debug ?
        $app['debug'] = $app['config.system']->getDebug();

        #
        # Site config service
        #

        $app['config.site'] = $app->share(function() use ($app, $filebackedconfig) {
            return new MozzaServices\SiteConfigService(
                $filebackedconfig->load($app['environment']->getDataDir() . '/config/config.yml')
            );
        });

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

        #
        # ORM; platform independent, but config may depend on it (in paas, the site config is stored in DB)
        #

        $app->register(new DoctrineOrmServiceProvider, array(
            "orm.proxies_dir" => $app['environment']->getCacheDir(),
            "orm.em.options" => array(
                "mappings" => array(
                    # Using actual filesystem paths
                    array(
                        'type' => 'yml',
                        'namespace' => 'Mozza\Core\Entity',
                        'path' => $app['environment']->getSrcDir() . '/Mozza/Core/Resources/config/doctrine',
                    ),
                ),
            ),
        ));

        #######################################################################
        # Persistent storage
        #######################################################################

        #
        # LocalFS Client
        #

        $app['fs.persistent'] = $app->share(function() use ($app) {
            return new MozzaServices\PersistentStorageLocalFSService(
                $app['environment']->getRootDir()
            );
        });

        /*$files = $app['fs.persistent']->getAll('data/posts', 'md');
        echo '<pre>';
        print_r($files);
        echo '</pre>';

        echo '<pre>';
        print_r($app['fs.persistent']->getOne('data/posts/about.md'));
        echo '</pre>';

        foreach($files as $file) {
            echo 'file:' . $file->getRelativePath() . '/' . $file->getRelativePathname() . '<br/>';
        }

        echo '<hr/>';

        echo $app['fs.persistent']->getOne('data/posts/about.md')->getRelativePath() . '/' . $file->getRelativePathname() . '<br />';

        die('fin');*/

        #
        # Resource filepath resolver
        #

        $app['resource.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\ResourceResolverService(
                $app['environment']->getWebDir(),
                $app['config.system']->getPostswebresdir()
            );
        });

        #
        # Post resource filepath resolver
        #

        $app['post.resource.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\PostResourceResolverService(
                $app['environment']->getWebDir(),
                $app['config.system']->getPostswebresdir()
            );
        });

        #
        # Post filepath resolver
        #

        $app['postfile.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\PostFileResolverService(
                $app['config.system']->getPostsdir(),
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
                $app['config.system']->getPostsdir(),
                $app['culture']
            );
        });
    }

    public function boot(Application $app) {
    }
}