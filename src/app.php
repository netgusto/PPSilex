<?php

use Silex\Application,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse;

#use Aws\S3\S3Client,
    #Aws\Common\Credentials\Credentials;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Controller\HomeController,
    Mozza\Core\Controller\PostController,
    Mozza\Core\Controller\FeedController,
    Mozza\Core\Controller\JsonController,
    Mozza\Core\Controller\ErrorController,
    Mozza\Core\Services as MozzaServices,
    Mozza\Core\Exception\PostNotFoundException,
    Mozza\Core\Twig\MozzaExtension as TwigMozzaExtension;

###############################################################################
# Keeping track of paths
###############################################################################

$rootdir = realpath(__DIR__ . '/..');
require_once $rootdir . '/vendor/autoload.php';

###############################################################################
# Initializing the application
###############################################################################

$app = new Application();
$app['version'] = '1.0.0';
$app['rootdir'] = $rootdir;

unset($rootdir); # from now on, we will only use the DI container's version

# Building a temporary root request to determine host url, as we cannot access the request service out of the scope of a controller
$rootrequest = Request::createFromGlobals();
$app['sitedomain'] = $rootrequest->getHost();
$app['siteurl'] = $rootrequest->getScheme() . '://' . $rootrequest->getHttpHost() . $rootrequest->getBaseUrl();

###############################################################################
# Loading environment
###############################################################################

$app['environment'] = $app->share(function() use ($app) {
    return new MozzaServices\EnvironmentService(
        $app['rootdir']
    );
});

# Loading platforms

$platformprovider = $app['environment']->getEnv('PLATFORM');
$app->register(new $platformprovider(), array(
    #"hello" => "world",
));

#######################################################################
# ORM
#######################################################################

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


###############################################################################
# Building config services
###############################################################################

#
# Culture Service
#

$app['culture'] = $app->share(function() use ($app) {
    return new MozzaServices\CultureService(
        $app['config']['culture']['locale'],
        $app['config']['culture']['date']['format'],
        $app['config']['culture']['date']['timezone']
    );
});

#
# System config service
#

$app['config.system'] = $app->share(function() use ($app) {
    return new MozzaServices\SystemConfigService(
        $app['config']['system']
    );
});

#
# Site config service
#

$app['config.site'] = $app->share(function() use ($app) {
    return new MozzaServices\SiteConfigService(
        $app['config']['site']
    );
});

###############################################################################
# Building config-based services
###############################################################################




$app['abspath.data'] = array(
    # Data pathes
    'theme' => $app['environment']->getWebDir() . '/vendor/' . $app['config.site']->getTheme(),
    'posts' => $app['environment']->getRootDir() . '/' . trim($app['config.system']->getPostsdir(), '/'),
    'customhtml' => $app['environment']->getRootDir() . '/app/customhtml/',
    'postsresources' => $app['environment']->getWebDir() . '/' . trim($app['config.system']->getPostswebresdir(), '/')
);

#
# Doctrine ORM Services
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
# URL Generator service
#

$app->register(new UrlGeneratorServiceProvider());

#
# URL absolutizer service
#
$app['url.absolutizer'] = $app->share(function() use ($app) {
    return new MozzaServices\URLAbsolutizerService(
        $app['siteurl'],
        $app['environment']->getWebDir()
    );
});

#
# Resource filepath resolver
#

$app['resource.resolver'] = $app->share(function() use ($app) {
    return new MozzaServices\ResourceResolverService(
        $app['environment']->getWebDir(),
        $app['abspath.data']['postsresources']
    );
});

#
# Post resource filepath resolver
#

$app['post.resource.resolver'] = $app->share(function() use ($app) {
    return new MozzaServices\PostResourceResolverService(
        $app['environment']->getWebDir(),
        $app['abspath.data']['postsresources']
    );
});

$app['post.urlgenerator'] = $app->share(function() use ($app) {
    return new MozzaServices\PostURLGeneratorService(
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
        'cache' => $app['rootdir'] . '/app/cache/twig',
        'strict_variables' => TRUE,
        'autoescape' => TRUE,
        'debug' => $app['debug'],
    ),
));

# Enabling debug (needs twig, so immediately after twig)

if($app['config.system']->getDebug()) {
    
    $app->register(new WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => $app['rootdir'] . '/app/cache/profiler',
    ));

} else {
    # If debug mode is disabled, we handle the error messages nicely
    $app->error(function (\Exception $e, $code) use ($app) {

        if($code === 404 || $e instanceof PostNotFoundException) {
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

# Allows to use service name as controller in route definition
$app->register(new ServiceControllerServiceProvider());

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    
    $twig->addGlobal('site', $app['config']['site']);
    $twig->addGlobal('config', $app['config']);

    $twig->addExtension(new \Twig_Extensions_Extension_Text($app));

    $twig->addExtension(
        new TwigMozzaExtension(
            $app['post.repository'],
            $app['post.serializer'],
            $app['url_generator'],
            $app['post.urlgenerator'],
            $app['markdown.processor'],
            $app['resource.resolver'],
            $app['post.resource.resolver'],
            $app['url.absolutizer'],
            $app['sitedomain'],
            $app['culture'],
            $app['config']
        )
    );

    return $twig;
}));

# Setting the theme namespace
$app['twig.loader.filesystem']->addPath($app['abspath.data']['theme'] . '/views', 'MozzaTheme');
$app['twig.loader.filesystem']->addPath($app['abspath.data']['customhtml'], 'Custom');



#
# Business logic related services
#

$app['markdown.processor'] = $app->share(function() use ($app) {
    return new MozzaServices\CebeMarkdownProcessorService();
});

$app['post.fingerprinter'] = $app->share(function() use ($app) {
    return new MozzaServices\PostFingerprinterService();
});

$app['postfile.topostconverter'] = $app->share(function() use ($app) {
    return new MozzaServices\PostFileToPostConverterService();
});

# Url to Post filepath resolver
$app['postfile.resolver'] = $app->share(function() use ($app) {
    return new MozzaServices\PostFileResolverService(
        $app['abspath.data']['posts'],
        $app['config.system']->getPostsExtension()
    );
});


$app['postfile.repository'] = $app->share(function() use ($app) {
    return new MozzaServices\PostFileRepositoryService(
        $app['postfile.resolver'],
        $app['postfile.reader'],
        $app['abspath.data']['posts'],
        $app['config.system']->getPostsExtension()
    );
});

$app['postfile.reader'] = $app->share(function() use ($app) {
    return new MozzaServices\PostFileReaderService(
        $app['postfile.resolver'],
        $app['post.resource.resolver'],
        $app['post.fingerprinter'],
        $app['culture'],
        $app['config.site']
    );
});

$app['post.serializer'] = $app->share(function() use ($app) {
    return new MozzaServices\PostSerializerService(
        $app['post.repository'],
        $app['markdown.processor'],
        $app['post.urlgenerator'],
        $app['url.absolutizer'],
        $app['post.resource.resolver'],
        $app['culture']
    );
});

$app['post.cachehandler'] = $app->share(function() use ($app) {
    return new MozzaServices\PostCacheHandlerService(
        $app['system.status'],
        $app['postfile.repository'],
        $app['post.repository'],
        $app['postfile.topostconverter'],
        $app['orm.em'],
        $app['abspath.data']['posts'],
        $app['culture']
    );
});

$app['system.status'] = $app->share(function() use ($app) {
    return new MozzaServices\SystemStatusService(
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
        $app['postfile.resolver'],
        $app['config']['home']['postsperpage']
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
        $app['post.repository'],
        $app['post.serializer'],
        $app['post.resource.resolver'],
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

# Home page with page > 1
$app->match('/page/{page}', 'home.controller:indexAction')
    ->assert('page', '[2-9]|[0-9]{2,}')
    ->bind('home_paged');

# Filename /rss or /atom: RSS Feed
$app->get('rss', 'feed.controller:indexAction')
    ->bind('rss');

# Filename /rss or /atom: RSS Feed
$app->get('json/posts', 'json.controller:indexAction')
    ->bind('json.posts');

# Filename /rss or /atom: RSS Feed
$app->get('json/posts/{slug}', 'json.controller:postAction')
    ->assert('slug', '.+')
    ->bind('json.post');

# Filename path/to/post.md: Single Post Pages
$app->get('{slug}', 'post.controller:indexAction')
    ->assert('slug', '.+')
    ->assert('slug', '^((?!_profiler).)*$')
    ->bind('post');

###############################################################################
# Handling cache
###############################################################################


$app->before(function(Request $req) use($app) {
    $app['post.cachehandler']->updateCacheIfNeeded();
});

# Serving the app
return $app;