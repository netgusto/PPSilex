<?php

namespace Mozza\Core\Provider\Platform;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\DoctrineServiceProvider;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

use Mozza\Core\Services as MozzaServices,
    Mozza\Core\Exception as MozzaException;

class ClassicPlatformServiceProvider implements ServiceProviderInterface {
    
    public function register(Application $app) {
        
        # Defaults:
        #   * Persistent file storage is Local FS
        #   * Database is sqlite DB

        #######################################################################
        # Database connection
        #######################################################################

        $dbresolver = new MozzaServices\DatabaseUrlResolverService();

        if(($databaseurl = $app['environment']->getEnv('DATABASE_URL')) === null) {
            $databaseurl = 'sqlite://' . $app['environment']->getCacheDir() . '/db/cache.db';
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

        #
        # System status service (needs ORM)
        #

        $app['system.status'] = $app->share(function() use ($app) {
            return new MozzaServices\Context\SystemStatusService(
                $app['orm.em']
            );
        });

        ###############################################################################
        # Config services
        ###############################################################################

        $parameters = array(
            'data.dir' => 'data',   # posts are in data/ with on the classic platform setup
        );

        #
        # Site config service
        #

        $configfile = $app['environment']->getRootDir() . '/data/config/config.yml';
        if(!is_file($configfile)) {
            $exception = new MozzaException\SiteConfigFileMissingException();
            $exception->setFilepath($configfile);
            throw $exception;
        }

        $app['config.site'] = $app->share(function() use ($app, $parameters, $configfile) {
            $filebackedconfig = new MozzaServices\Config\Loader\FileBackedConfigLoaderService($parameters);
            return new MozzaServices\Config\SiteConfigService(
                $filebackedconfig->load($configfile)
            );
        });

        #######################################################################
        # Persistent storage
        #######################################################################

        #
        # LocalFS Client
        #

        if(!is_null($app['environment']->getEnv('STORAGE'))) {
            throw new \UnexpectedValueException('STORAGE can not be set in environment using Classic Platform Provider.');
        }

        $app['fs.persistent'] = $app->share(function() use ($app) {
            return new MozzaServices\PersistentStorage\LocalFSPersistentStorageService(
                $app['environment']->getRootDir(),
                $app['environment']->getSiteUrl()
            );
        });

        #
        # Resource filepath resolver
        #

        $app['resource.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\ResourceResolverService(
                $app['fs.persistent'],
                $app['config.site']->getResourcesdir()
            );
        });

        #
        # Post resource filepath resolver
        #

        $app['post.resource.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\Post\PostResourceResolverService(
                $app['fs.persistent'],
                $app['config.site']->getResourcesdir()
            );
        });

        #
        # Post filepath resolver
        #

        $app['postfile.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\PostFile\PostFileResolverService(
                $app['config.site']->getPostsdir(),
                $app['config.site']->getPostsExtension()
            );
        });

        #
        # Post cache handler
        #

        $app['post.cachehandler'] = $app->share(function() use ($app) {
            return new MozzaServices\CacheHandler\LastModifiedPostCacheHandlerService(
                $app['fs.persistent'],
                $app['system.status'],
                $app['postfile.repository'],
                $app['post.repository'],
                $app['postfile.topostconverter'],
                $app['orm.em'],
                $app['config.site']->getPostsdir(),
                $app['culture']
            );
        });
    }

    public function boot(Application $app) {
    }
}