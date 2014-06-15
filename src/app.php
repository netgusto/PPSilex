<?php

use Silex\Application,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider,
    Silex\Provider\DoctrineServiceProvider;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Controller\HomeController,
    Mozza\Core\Controller\PostController,
    Mozza\Core\Controller\FeedController,
    Mozza\Core\Controller\JsonController,
    Mozza\Core\Controller\ErrorController,
    Mozza\Core\Services\SystemStatusService,
    Mozza\Core\Services\URLAbsolutizerService,
    Mozza\Core\Services\PostFileResolverService,
    Mozza\Core\Services\PostFingerprinterService,
    Mozza\Core\Services\PostFileToPostConverterService,
    Mozza\Core\Services\PostFileReaderService,
    Mozza\Core\Services\PostFileRepositoryService,
    Mozza\Core\Services\PostURLGeneratorService,
    Mozza\Core\Services\PostResourceResolverService,
    Mozza\Core\Services\PostSerializerService,
    Mozza\Core\Services\PostCacheHandlerService,
    Mozza\Core\Services\CebeMarkdownProcessorService,
    Mozza\Core\Twig\MozzaExtension as TwigMozzaExtension;

###############################################################################
# Keeping track of paths
###############################################################################

define('ROOT_DIR', realpath(__DIR__ . '/..'));
require_once ROOT_DIR . '/vendor/autoload.php';

###############################################################################
# Initializing the application
###############################################################################

$app = new Application();
$app['version'] = '1.0.0';

# Building a temporary root request to determine host url, as we cannot access the request service out of the scope of a controller
$rootrequest = Request::createFromGlobals();
$app['sitedomain'] = $rootrequest->getHost();
$app['siteurl'] = $rootrequest->getScheme() . '://' . $rootrequest->getHttpHost() . $rootrequest->getBaseUrl();

###############################################################################
# Configuring the application
###############################################################################

$configfile = ROOT_DIR . '/app/parameters.yml';

if(!file_exists($configfile)) {
    # If no config: run wizard
} else {
    $app->register(new DerAlex\Silex\YamlConfigServiceProvider($configfile));
}

if(isset($app['config']['debug']) && is_bool($app['config']['debug'])) {
    $app['debug'] = $app['config']['debug'];
} else {
    $app['debug'] = FALSE;  # debug is disabled by default
}

# Building the realpathes for configures pathes

$webdir = ROOT_DIR . '/web';
$app['abspath'] = array(
    'root' => ROOT_DIR,
    'posts' => ROOT_DIR . '/' . trim($app['config']['posts']['dir'], '/'),
    'customhtml' => ROOT_DIR . '/app/customhtml/',
    'web' => $webdir,
    'theme' => $webdir . '/vendor/' . $app['config']['site']['theme'],
    'postsresources' => $webdir . '/' . trim($app['config']['posts']['webresdir'], '/'),
    'app' => ROOT_DIR . '/app',
    'cache' => ROOT_DIR . '/app/cache',
    'source' => ROOT_DIR . '/src',
    'cachedbproxies' => ROOT_DIR . '/app/cache/db/proxies',
    'cachedbsqlite' => ROOT_DIR . '/app/cache/db/cache.db',
);

# Setting server timezone and locale
date_default_timezone_set($app['config']['date']['timezone']);
setlocale(LC_ALL, $app['config']['site']['locale']);
$app['timezone'] = new \DateTimeZone($app['config']['date']['timezone']);

###############################################################################
# Building services
###############################################################################

#
# Doctrine and Doctrine ORM Services
#

$app->register(new DoctrineServiceProvider, array(
    'db.options' => array(
        'driver' => 'pdo_sqlite',
        'path' => $app['abspath']['cachedbsqlite'],
    ),
    /*'db.options'    => array(
        'driver'        => 'pdo_mysql',
        'host'          => 'localhost',
        'dbname'        => 'mozza',
        'user'          => 'root',
        'password'      => '',
        'charset'       => 'utf8',
        'driverOptions' => array(1002 => 'SET NAMES utf8'),
    )*/
));

$app->register(new DoctrineOrmServiceProvider, array(
    "orm.proxies_dir" => $app['abspath']['cachedbproxies'],
    "orm.em.options" => array(
        "mappings" => array(
            # Using actual filesystem paths
            array(
                'type' => 'yml',
                'namespace' => 'Mozza\Core\Entity',
                'path' => $app['abspath']['source'] . '/Mozza/Core/Resources/config/doctrine',
            ),
        ),
    ),
));

#
# URL Generator service
#

$app->register(new UrlGeneratorServiceProvider());

#
# URL absolutizer service
#
$app['url.absolutizer'] = $app->share(function() use ($app) {
    return new URLAbsolutizerService(
        $app['siteurl'],
        $app['abspath']['web']
    );
});

#
# Post resource filepath resolver
#

$app['post.resource.resolver'] = $app->share(function() use ($app) {
    return new PostResourceResolverService(
        $app['abspath']['web'],
        $app['abspath']['postsresources']
    );
});

$app['post.urlgenerator'] = $app->share(function() use ($app) {
    return new PostURLGeneratorService(
        $app['post.repository'],
        $app['url_generator'],
        $app['url.absolutizer']
    );
});

#
# Templating Service
#

$app->register(new TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => ROOT_DIR . '/app/cache/twig',
        'strict_variables' => TRUE,
        'autoescape' => TRUE,
        'debug' => TRUE,
    ),
));

# Allows to use service name as controller in route definition
$app->register(new ServiceControllerServiceProvider());

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    
    $twig->addGlobal('site', $app['config']['site']);
    $twig->addGlobal('config', $app['config']);

    $twig->addExtension(new \Twig_Extension_StringLoader());

    $twig->addExtension(
        new TwigMozzaExtension(
            $app['post.repository'],
            $app['post.serializer'],
            $app['url_generator'],
            $app['post.urlgenerator'],
            $app['markdown.processor'],
            $app['post.resource.resolver'],
            $app['url.absolutizer'],
            $app['sitedomain'],
            $app['config']
        )
    );

    return $twig;
}));

# Setting the theme namespace
$app['twig.loader.filesystem']->addPath($app['abspath']['theme'] . '/views', 'MozzaTheme');
$app['twig.loader.filesystem']->addPath($app['abspath']['customhtml'], 'Custom');

#
# Web profiler service
#

if($app['debug']) {
    $app->register(new WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => ROOT_DIR . '/app/cache/profiler',
    ));
}

#
# Business logic related services
#

$app['markdown.processor'] = $app->share(function() use ($app) {
    return new CebeMarkdownProcessorService();
});

$app['post.fingerprinter'] = $app->share(function() use ($app) {
    return new PostFingerprinterService();
});

$app['postfile.topostconverter'] = $app->share(function() use ($app) {
    return new PostFileToPostConverterService();
});

# Url to Post filepath resolver
$app['postfile.resolver'] = $app->share(function() use ($app) {
    return new PostFileResolverService(
        $app['abspath']['posts'],
        $app['config']['posts']['extension']
    );
});


$app['postfile.repository'] = $app->share(function() use ($app) {
    return new PostFileRepositoryService(
        $app['postfile.resolver'],
        $app['postfile.reader'],
        $app['abspath']['posts'],
        $app['config']['posts']['extension']
    );
});

$app['postfile.reader'] = $app->share(function() use ($app) {
    return new PostFileReaderService(
        $app['postfile.resolver'],
        $app['post.resource.resolver'],
        $app['post.fingerprinter'],
        $app['timezone'],
        $app['config']
    );
});

$app['post.serializer'] = $app->share(function() use ($app) {
    return new PostSerializerService(
        $app['post.repository'],
        $app['markdown.processor'],
        $app['post.urlgenerator'],
        $app['url.absolutizer'],
        $app['post.resource.resolver'],
        $app['config']
    );
});

$app['post.cachehandler'] = $app->share(function() use ($app) {
    return new PostCacheHandlerService(
        $app['system.status'],
        $app['postfile.repository'],
        $app['post.repository'],
        $app['postfile.topostconverter'],
        $app['orm.em'],
        $app['abspath']['posts'],
        $app['timezone']
    );
});

$app['system.status'] = $app->share(function() use ($app) {
    return new SystemStatusService(
        $app['orm.em']
    );
});

#
# Data repositories Services
#

$app['post.repository'] = $app->share(function() use ($app) {
    return new PostRepository(
        $app['orm.em']
    );
});

#
# Controller Services
#

# The controller responsible for the homepage
$app['home.controller'] = $app->share(function() use ($app) {
    return new HomeController(
        $app['twig'],
        $app['post.repository'],
        $app['postfile.resolver']
    );
});

# The controller responsible for displaying a post
$app['post.controller'] = $app->share(function() use ($app) {
    return new PostController(
        $app['twig'],
        $app['post.repository'],
        $app['postfile.resolver']
    );
});

# The controller responsible for the RSS/Atoms feeds
$app['feed.controller'] = $app->share(function() use ($app) {
    return new FeedController(
        $app['twig'],
        $app['post.repository'],
        $app['markdown.processor'],
        $app['url_generator'],
        $app['url.absolutizer']
    );
});

# The controller responsible for the JSON feed
$app['json.controller'] = $app->share(function() use ($app) {
    return new JsonController(
        $app['post.repository'],
        $app['post.serializer']
    );
});

# The controller responsible for error handling
$app['error.controller'] = $app->share(function() use ($app) {
    return new ErrorController(
        $app['twig']
    );
});



###############################################################################
# Routing the request
###############################################################################

# Filename empty: The Home Page (All Posts)
$app->get('/', 'home.controller:indexAction')
    ->bind('home');

# Filename /rss or /atom: RSS Feed
$app->get('rss', 'feed.controller:indexAction')
    ->bind('feed');

# Filename /rss or /atom: RSS Feed
$app->get('json/posts', 'json.controller:indexAction')
    ->bind('json.posts');

# Filename /rss or /atom: RSS Feed
$app->get('json/posts/{slug}', 'json.controller:postAction')
    ->assert('slug', '.+')
    ->bind('json.post');

# Filename path/to/post.md: Single Post Pages
$app->match('blog/{slug}', 'post.controller:indexAction')
    ->assert('slug', '.+')
    ->bind('post');

# Filename path/to/post.md: Single Post Pages
$app->match('blog{trailingslash}', function(Request $request) use ($app) {
    return new RedirectResponse($app['url_generator']->generate('home'));
})->assert('trailingslash', '/?');


if(!$app['debug']) {
    # If debug mode is disabled, we handle the error messages nicely
    $app->error(function (\Exception $e, $code) use ($app) {

        if($code === 404) {
            return $app['error.controller']->notFoundAction(
                $app['request'],
                $app,
                $e,
                $code
            );
        }

        return $app['error.controller']->errorAction(
            $app['request'],
            $app,
            $e,
            $code
        );
    });
}

###############################################################################
# Handling cache
###############################################################################


$app->before(function(Request $req) use($app) {
    $app['post.cachehandler']->updateCacheIfNeeded();
});

# Serving the app
return $app;