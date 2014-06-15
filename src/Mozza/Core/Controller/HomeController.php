<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Twig_Environment;

use Mozza\Core\Entity\Post,
    Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\PostFileResolverService;

class HomeController {

    protected $twig;
    protected $postRepo;
    protected $postpathresolver;

    public function __construct(Twig_Environment $twig, PostRepository $postRepo, PostFileResolverService $postpathresolver) {
        $this->twig = $twig;
        $this->postRepo = $postRepo;
        $this->postpathresolver = $postpathresolver;
    }

    public function indexAction(Request $request, Application $app) {

        /*$post = new Post();
        $post->setTitle('Test post !');
        $post->setSlug('no-post');
        $post->setIntro("It looks like you don't have any post in your blog yet. To add a post, create a file in `data/posts`.");
        $post->setAuthor($app['config']['site']['owner']['name']);
        $post->setDate(new \DateTime());
        $post->setStatus('published');
        $app['orm.em']->persist($post);
        $app['orm.em']->flush();
        die('laaa');*/

        $posts = $this->postRepo->findAll();
        if(count($posts) === 0) {
            $post = new Post();
            $post->setTitle('Oh no ! not a single post to display !');
            $post->setSlug('no-post');
            $post->setIntro("It looks like you don't have any post in your blog yet. To add a post, create a file in `data/posts`.");
            $post->setAuthor($app['config']['site']['owner']['name']);
            $post->setMeta(array(
                'comments' => 'off'
            ));
            return $this->twig->render('@MozzaTheme/Post/index.html.twig', array(
                'post' => $post,
            ));
        }

        return $app['post.controller']->indexAction($request, $app, $posts[0]->getSlug());
    }
}