<?php

namespace Pulpy\Core\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Pulpy\Core\Services as PulpyServices,
    Pulpy\Core\Repository\PostRepository;

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;

class BusinessLogicServiceProvider implements ServiceProviderInterface {

    public function register(Application $app) {

        #
        # Data repositories Services
        #

        $app['postfile.repository'] = $app->share(function() use ($app) {
            return new PulpyServices\PostFile\PostFileRepositoryService(
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
            return new PulpyServices\TextProcessor\Markdown\CebeMarkdownProcessorService();
        });

        $app['post.fingerprinter'] = $app->share(function() use ($app) {
            return new PulpyServices\Post\PostFingerprinterService();
        });

        $app['postfile.topostconverter'] = $app->share(function() use ($app) {
            return new PulpyServices\PostFile\PostFileToPostConverterService();
        });

        $app['post.urlgenerator'] = $app->share(function() use ($app) {
            return new PulpyServices\Post\PostURLGeneratorService(
                $app['post.repository'],
                $app['url_generator'],
                $app['url.absolutizer']
            );
        });

        $app['postfile.reader'] = $app->share(function() use ($app) {
            return new PulpyServices\PostFile\PostFileReaderService(
                $app['fs.persistent'],
                $app['postfile.resolver'],
                $app['post.resource.resolver'],
                $app['post.fingerprinter'],
                $app['culture'],
                $app['config.site']
            );
        });

        $app['post.serializer'] = $app->share(function() use ($app) {
            return new PulpyServices\Post\PostSerializerService(
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