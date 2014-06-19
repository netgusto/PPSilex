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

        $app['markdown.processor'] = $app->share(function() use ($app) {
            return new MozzaServices\CebeMarkdownProcessorService();
        });

        $app['post.fingerprinter'] = $app->share(function() use ($app) {
            return new MozzaServices\PostFingerprinterService();
        });

        $app['postfile.topostconverter'] = $app->share(function() use ($app) {
            return new MozzaServices\PostFileToPostConverterService();
        });

        $app['post.urlgenerator'] = $app->share(function() use ($app) {
            return new MozzaServices\PostURLGeneratorService(
                $app['post.repository'],
                $app['url_generator'],
                $app['url.absolutizer']
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
    }

    public function boot(Application $app) {
    }
}