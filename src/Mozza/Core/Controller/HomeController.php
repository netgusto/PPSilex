<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Twig_Environment;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\PostResolverService;

class HomeController {

    protected $twig;
    protected $postRepo;
    protected $postpathresolver;

    public function __construct(Twig_Environment $twig, PostRepository $postRepo, PostResolverService $postpathresolver) {
        $this->twig = $twig;
        $this->postRepo = $postRepo;
        $this->postpathresolver = $postpathresolver;
    }

    public function indexAction(Request $request, Application $app) {
        /*return $this->twig->render('@MozzaTheme/Home/Index.html.twig', array(
            'posts' => $this->postRepo->findAll(),
        ));*/

        $posts = $this->postRepo->findAll();
        return $app['post.controller']->indexAction($request, $app, $posts[0]->getSlug());
    }
}