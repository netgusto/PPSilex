<?php

namespace Mozza\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Mozza\Core\Services as MozzaServices,
    Mozza\Core\Repository\PostRepository;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

class BusinessLogicServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {

        $app['system.status'] = $app->share(function() use ($app) {
            return new MozzaServices\Context\SystemStatusService(
                $app['orm.em']
            );
        });

        #
        # Data repositories Services
        #

        $app['postfile.repository'] = $app->share(function() use ($app) {
            return new MozzaServices\PostFile\PostFileRepositoryService(
                $app['fs.persistent'],
                $app['postfile.resolver'],
                $app['postfile.reader'],
                $app['config.site']->getPostsdir(),
                $app['config.site']->getPostsExtension()
            );
        });

        $app['post.repository'] = $app->share(function() use ($app) {
            return new PostRepository(
                $app['orm.em']
            );
        });

        $app['markdown.processor'] = $app->share(function() use ($app) {
            return new MozzaServices\TextProcessor\Markdown\CebeMarkdownProcessorService();
        });

        $app['post.fingerprinter'] = $app->share(function() use ($app) {
            return new MozzaServices\Post\PostFingerprinterService();
        });

        $app['postfile.topostconverter'] = $app->share(function() use ($app) {
            return new MozzaServices\PostFile\PostFileToPostConverterService();
        });

        $app['post.urlgenerator'] = $app->share(function() use ($app) {
            return new MozzaServices\Post\PostURLGeneratorService(
                $app['post.repository'],
                $app['url_generator'],
                $app['url.absolutizer']
            );
        });

        $app['postfile.reader'] = $app->share(function() use ($app) {
            return new MozzaServices\PostFile\PostFileReaderService(
                $app['fs.persistent'],
                $app['postfile.resolver'],
                $app['post.resource.resolver'],
                $app['post.fingerprinter'],
                $app['culture'],
                $app['config.site']
            );
        });

        $app['post.serializer'] = $app->share(function() use ($app) {
            return new MozzaServices\Post\PostSerializerService(
                $app['post.repository'],
                $app['markdown.processor'],
                $app['post.urlgenerator'],
                $app['url.absolutizer'],
                $app['post.resource.resolver'],
                $app['culture']
            );
        });
    }

    public function boot(Application $app) {
    }
}