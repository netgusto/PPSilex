<?php

namespace Mozza\Core\Services;

use Silex\Application,
    DerAlex\Silex\YamlConfigServiceProvider;

class PlatformTraditionalService {
    
    public function __construct(Application $app) {
        
        ###############################################################################
        # Configuring the application
        ###############################################################################

        $configfile = $app['path.rootdir'] . '/app/config/config.yml';
        $app->register(new YamlConfigServiceProvider($configfile));
    }
}