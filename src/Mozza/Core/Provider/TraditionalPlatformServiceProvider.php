<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\DoctrineServiceProvider;

use DerAlex\Silex\YamlConfigServiceProvider;

use Mozza\Core\Services as MozzaServices;

class TraditionalPlatformServiceProvider implements ServiceProviderInterface {
    
    public function register(Application $app) {
        
        ###############################################################################
        # Registering the config service
        ###############################################################################

        $configfile = $app['environment']->getRootDir() . '/app/config/config.yml';
        $app->register(new YamlConfigServiceProvider($configfile));

        # Debug ?
        $app['debug'] = $app['config']['system']['debug'];

        ###############################################################################
        # Registering the DB service
        ###############################################################################

        $dbresolver = new MozzaServices\DatabaseUrlResolverService();

        if(($databaseurl = $app['environment']->getEnv('DATABASE_URL')) === null) {
            throw new \UnexpectedValueException('DATABASE_URL is not set in environment.');
        }

        $this->databasedsn = $dbresolver->resolve($databaseurl);

        #######################################################################
        # Database connection
        #######################################################################

        $app->register(new DoctrineServiceProvider, array(
            'db.options' => $this->databasedsn,
        ));
    }

    public function boot(Application $app) {
    }
}