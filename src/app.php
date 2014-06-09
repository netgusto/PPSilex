<?php

use Silex\Application,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Symfony\Component\HttpFoundation\Request;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Controller\HomeController,
    Mozza\Core\Controller\PostController,
    Mozza\Core\Controller\FeedController,
    Mozza\Core\Services\URLAbsolutizerService,
    Mozza\Core\Services\PostResolverService,
    Mozza\Core\Services\PostResourceResolverService,
    Mozza\Core\Services\PostReaderService,
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

# Building a temporary root request to determine host url, as we cannot access the request service out of the scope of a controller
$rootrequest = Request::createFromGlobals();
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
);

# Setting server timezone and locale
date_default_timezone_set($app['config']['date']['timezone']);
setlocale(LC_ALL, $app['config']['site']['locale']);
$app['timezone'] = new \DateTimeZone($app['config']['date']['timezone']);

###############################################################################
# Building services
###############################################################################

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
            $app['url_generator'],
            $app['markdown.processor'],
            $app['post.resource.resolver'],
            $app['url.absolutizer'],
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

$app->register(new WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => ROOT_DIR . '/app/cache/profiler',
));

#
# Business logic related services
#

$app['markdown.processor'] = $app->share(function() use ($app) {
    return new CebeMarkdownProcessorService();
});

# Url to Post filepath resolver
$app['post.resolver'] = $app->share(function() use ($app) {
    return new PostResolverService(
        $app['abspath']['posts'],
        $app['config']['posts']['extension']
    );
});

$app['post.reader'] = $app->share(function() use ($app) {
    return new PostReaderService(
        $app['post.resolver'],
        $app['post.resource.resolver'],
        $app['timezone'],
        $app['config']
    );
});


#
# Data repositories Services
#

$app['post.repository'] = $app->share(function() use ($app) {
    return new PostRepository(
        $app['post.resolver'],
        $app['post.reader'],
        $app['abspath']['posts'],
        $app['config']['posts']['extension']
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
        $app['post.resolver']
    );
});

# The controller responsible for displaying a post
$app['post.controller'] = $app->share(function() use ($app) {
    return new PostController(
        $app['twig'],
        $app['post.repository'],
        $app['post.resolver']
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



###############################################################################
# Routing the request
###############################################################################

# Filename empty: The Home Page (All Posts)
$app->get('/', 'home.controller:indexAction')
    ->bind('home');

# Filename /rss or /atom: RSS Feed
$app->get('rss', 'feed.controller:indexAction')
    ->bind('feed');

# Filename path/to/post.md: Single Post Pages
$app->match('blog/{slug}', 'post.controller:indexAction')->assert('slug', '.+')
    ->bind('post');

# Serving the app
return $app;