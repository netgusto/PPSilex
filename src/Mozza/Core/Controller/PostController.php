<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Twig_Environment;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\PostResolverService,
    Mozza\Core\Exception;

class PostController {

    protected $twig;
    protected $postRepo;
    protected $postresolver;

    public function __construct(Twig_Environment $twig, PostRepository $postRepo, PostResolverService $postresolver) {
        $this->twig = $twig;
        $this->postRepo = $postRepo;
        $this->postresolver = $postresolver;
    }

    public function indexAction(Request $request, Application $app, $slug) {
        
        $post = $this->postRepo->findBySlug($slug);
        if(!$post) {
            throw new Exception\PostNotFoundException('Post with slug ' . $slug . ' does not exist.');
        }

        $nextpost = $this->postRepo->findNext($post);
        $previouspost = $this->postRepo->findPrevious($post);

        return $this->twig->render('@MozzaTheme/Post/index.html.twig', array(
            'post' => $post,
            'nextpost' => $nextpost,
            'previouspost' => $previouspost,
        ));
    }
}