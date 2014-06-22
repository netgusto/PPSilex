<?php

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;

use Mozza\Core\Services as MozzaServices,
    Mozza\Core\Provider as MozzaProvider,
    Mozza\Core\Exception as MozzaException;

# Here we go

$rootdir = realpath(__DIR__ . '/..');
require_once $rootdir . '/vendor/autoload.php';

###############################################################################
# Initializing the application
###############################################################################

$app = new Application();
$app['version'] = '1.0.0';
$app['rootdir'] = $rootdir;
unset($rootdir); # from now on, we will only use the DI container's version

###############################################################################
# Loading environment
###############################################################################

$app['scalar.interpreter'] = $app->share(function() use ($app) {
    return new MozzaServices\ScalarInterpreterService();
});

$app['environment'] = $app->share(function() use ($app) {
    
    # Resolving environment (merging env with dotenv file if present)
    $environmentresolver = new MozzaServices\Context\EnvironmentResolverService(
        $app['rootdir'] . '/.env'
    );
    
    return new MozzaServices\Context\EnvironmentService(
        $environmentresolver->getResolvedEnv(),
        $app['scalar.interpreter'],
        $app['rootdir']
    );
});

# Debug ?
$app['debug'] = $app['environment']->getDebug();

###############################################################################
# Mounting platform (infrastructure services we rely upon)
###############################################################################

$platformprovider = $app['environment']->getEnv('PLATFORM');
if(!$platformprovider) {
    $platformprovider = 'Mozza\Core\Provider\Platform\ClassicPlatformServiceProvider';
}
$app->register(new $platformprovider());

# We now have:
#
# * an environment
# * a loaded app configuration (config.site)
# * a database connection
# * a persistent storage

###############################################################################
# Building platform independent or platform-abstracted services
###############################################################################

$app->register(new MozzaProvider\LowLevelServiceProvider());

###############################################################################
# Building business logic services
###############################################################################

$app->register(new MozzaProvider\BusinessLogicServiceProvider());

###############################################################################
# Building controller services
###############################################################################

$app->register(new MozzaProvider\ControllerProvider());

$app->before(function(Request $req) use($app) {
    
    ###############################################################################
    # Checking if all systems are GO
    ###############################################################################
    if($app['system.status']->getInitialized() !== TRUE) {
        throw new MozzaException\InitializationNeeded\SystemStatusMarkedAsUninitializedInitializationNeededException();
    }

    ###############################################################################
    # Handling cache
    ###############################################################################
    $app['post.cachehandler']->updateCacheIfNeeded();
});

# Serving the app
return $app;