<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Routing\Generator\UrlGenerator,
    Twig_Environment;

use \Suin\RSSWriter\Feed,
    \Suin\RSSWriter\Channel,
    \Suin\RSSWriter\Item;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\MarkdownProcessorInterface,
    Mozza\Core\Services\URLAbsolutizerService;

class FeedController {

    protected $twig;
    protected $postRepo;
    protected $markdownprocessor;
    protected $urlgenerator;

    public function __construct(Twig_Environment $twig, PostRepository $postRepo, MarkdownProcessorInterface $markdownprocessor, UrlGenerator $urlgenerator, URLAbsolutizerService $urlabsolutizer) {
        $this->twig = $twig;
        $this->postRepo = $postRepo;
        $this->markdownprocessor = $markdownprocessor;
        $this->urlgenerator = $urlgenerator;
        $this->urlabsolutizer = $urlabsolutizer;
    }

    public function indexAction(Request $request, Application $app, $feedtype) {

        $feed = new Feed();
        $channel = new Channel();
        $channel
            ->title($app['config']['site']['title'])
            ->description($app['config']['site']['description'])
            ->url($this->urlabsolutizer->absoluteSiteURL())
            ->appendTo($feed);

        $posts = $this->postRepo->findAll();
        foreach($posts as $post) {

            $postcontent = trim($this->markdownprocessor->toHtml($post->getIntro()));

            $item = new Item();
            $item
                ->title($post->getTitle())
                ->description($postcontent)
                ->pubdate($post->getDate()->getTimestamp())
                ->url($this->urlabsolutizer->absoluteURLFromRoutePath($this->urlgenerator->generate('post', array('slug' => $post->getSlug()))))
                ->appendTo($channel);
        }

        $response = new Response(
            $feed->__toString(),
            200,
            array(
                'content-type' => 'application/rss+xml'
            )
        );

        return $response;
    }
}