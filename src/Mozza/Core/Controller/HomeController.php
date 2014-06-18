<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Twig_Environment;

use Mozza\Core\Entity\Post,
    Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\PostFileResolverService;

class HomeController {

    protected $twig;
    protected $postRepo;
    protected $postpathresolver;
    protected $postsperpage;

    public function __construct(Twig_Environment $twig, PostRepository $postRepo, PostFileResolverService $postpathresolver, $postsperpage = 5) {
        $this->twig = $twig;
        $this->postRepo = $postRepo;
        $this->postpathresolver = $postpathresolver;
        $this->postsperpage = $postsperpage;
    }

    public function indexAction(Request $request, Application $app, $page=1) {

        $nbposts = $this->postRepo->count();
        if($nbposts === 0) {
            
            $post = new Post();
            $post->setTitle('Oh no ! not a single post to display !');
            $post->setSlug('no-post');
            $post->setIntro("It looks like you don't have any post in your blog yet. To add a post, create a file in `data/posts`.");
            $post->setAuthor($app['config']['site']['owner']['name']);
            $post->setComments(FALSE);

            return $this->twig->render('@MozzaTheme/Post/index.html.twig', array(
                'post' => $post,
            ));
        }
        
        $nbpages = ceil($nbposts / $this->postsperpage);
        $posts = $this->postRepo->findAllAtPage($page, $this->postsperpage);

        if($page > $nbpages) {
            return new RedirectResponse($app['url_generator']->generate('home'));
        }

        return $this->twig->render('@MozzaTheme/Home/index.html.twig', array(
            'posts' => $posts,
            'page' => $page,
            'nbpages' => $nbpages,
        ));
    }
}