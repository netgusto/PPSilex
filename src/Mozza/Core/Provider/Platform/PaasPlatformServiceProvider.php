<?php

namespace Mozza\Core\Provider\Platform;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\DoctrineServiceProvider;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

use Mozza\Core\Services as MozzaServices;

class PaasPlatformServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {

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
            'data.dir' => '',
        );

        #
        # Site config service
        #

        $app['config.site'] = $app->share(function() use ($app, $parameters) {
            
            #debug_print_backtrace();
            #die();
            $dbbackedconfig = new MozzaServices\Config\Loader\DbBackedConfigLoaderService(
                $app['orm.em'],
                $parameters
            );

            return new MozzaServices\Config\SiteConfigService(
                $dbbackedconfig->load('config.site')
            );
        });

        #######################################################################
        # Persistent storage
        #######################################################################

        #
        # S3 Client
        #

        $storage = $app['environment']->getEnv('STORAGE');

        if($storage === 'S3') {

            $app['fs.persistent'] = $app->share(function() use ($app) {
                
                $s3_bucket = $app['environment']->getEnv('S3_BUCKET');
                $s3_keyid = $app['environment']->getEnv('S3_KEYID');
                $s3_secret = $app['environment']->getEnv('S3_SECRET');
                $s3_httpbaseurl = $app['environment']->getEnv('S3_HTTPBASEURL');
                
                if(empty($s3_httpbaseurl)) {
                    $s3_httpbaseurl = $app['environment']->getScheme() . '://' . $s3_bucket . '.s3.amazonaws.com';
                }

                return new MozzaServices\PersistentStorage\S3PersistentStorageService(
                    $s3_bucket,
                    $s3_keyid,
                    $s3_secret,
                    $s3_httpbaseurl
                );
            });
        } else {
            if(trim($storage) === '') {
                throw new \InvalidArgumentException('STORAGE engine should be set in the environment.');
            } else {
                throw new \InvalidArgumentException('Unsupported STORAGE engine ' . $storage . ' for PAAS platform.');
            }
        }

        #
        # Resource path resolver
        #

        $app['resource.resolver'] = $app->share(function() use ($app) {
            return new MozzaServices\ResourceResolverService(
                $app['fs.persistent'],
                $app['config.site']->getResourcesdir()
            );
        });

        #
        # Post resource path resolver
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
            return new MozzaServices\CacheHandler\EventedPostCacheHandlerService(
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