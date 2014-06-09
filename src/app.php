<?php

use Silex\Application,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Controller\HomeController,
    Mozza\Core\Controller\PostController,
    Mozza\Core\Controller\FeedController,
    Mozza\Core\Services\PostResolverService,
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
$app['debug'] = true;


###############################################################################
# Configuring the application
###############################################################################

$configfile = ROOT_DIR . '/app/parameters.yml';

if(!file_exists($configfile)) {
    # If no config: run wizard
} else {
    $app->register(new DerAlex\Silex\YamlConfigServiceProvider($configfile));
}

# Building the realpathes for configures pathes
$app['abspath'] = array(
    'root' => ROOT_DIR,
    'theme' => ROOT_DIR . '/web/vendor/' . $app['config']['site']['theme'],
    'posts' => ROOT_DIR . '/' . trim($app['config']['posts']['dir'], '/'),
    'customhtml' => ROOT_DIR . '/app/customhtml/',
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
        $app['timezone']
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
        $app['url_generator']
    );
});



###############################################################################
# Routing the request
###############################################################################

# Filename empty: The Home Page (All Posts)
$app->get('/', 'home.controller:indexAction')
    ->bind('home');

# Filename /rss or /atom: RSS Feed
$app->get('{feedtype}', 'feed.controller:indexAction')->assert('feedtype', 'rss|atom')
    ->bind('feed');

# Filename path/to/post.md: Single Post Pages
$app->match('blog/{slug}', 'post.controller:indexAction')->assert('slug', '.+')
    ->bind('post');

# Serving the app
return $app;