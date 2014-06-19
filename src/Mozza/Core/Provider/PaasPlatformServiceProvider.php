<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\DoctrineServiceProvider;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

use Mozza\Core\Services as MozzaServices;

class PaasPlatformServiceProvider implements ServiceProviderInterface {

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
        # Site config service
        #

        $dbbackedconfig = new MozzaServices\DbBackedConfigLoaderService(
            $app['orm.em'],
            $parameters
        );

        $app['config.site'] = $app->share(function() use ($app, $dbbackedconfig) {
            return new MozzaServices\SiteConfigService(
                $dbbackedconfig->load('config.site')
            );
        });

        #######################################################################
        # Persistent storage
        #######################################################################

        #
        # S3 Client
        #

        if($app['environment']->getEnv('STORAGE') === 'S3') {

            $app['fs.persistent'] = $app->share(function() use ($app) {
                
                $s3_bucket = $app['environment']->getEnv('S3_BUCKET');
                $s3_keyid = $app['environment']->getEnv('S3_KEYID');
                $s3_secret = $app['environment']->getEnv('S3_SECRET');

                return new MozzaServices\PersistentStorageS3Service(
                    $s3_bucket,
                    $s3_keyid,
                    $s3_secret
                );
            });
        } else {
            throw new \InvalidArgumentException('Unsupported STORAGE engine for PAAS platform.');
        }

        /*$files = $app['fs.persistent']->getAll('posts', 'md');
        echo '<pre>';
        print_r($files);
        echo '</pre>';

        echo '<pre>';
        print_r($app['fs.persistent']->getOne('posts/about.md'));
        echo '</pre>';

        foreach($files as $file) {
            echo 'file:' . $file->getRelativePath() . '/' . $file->getRelativePathname() . '<br/>';
        }

        echo '<hr/>';

        echo $app['fs.persistent']->getOne('posts/about.md')->getRelativePath() . '/' . $file->getRelativePathname() . '<br />';

        die('FIN');*/

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