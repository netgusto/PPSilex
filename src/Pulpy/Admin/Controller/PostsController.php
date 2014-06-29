<?php

namespace Pulpy\Admin\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Twig_Environment;

use Pulpy\Core\Entity\Post,
    Pulpy\Core\Repository\PostRepository;

class PostsController {

    protected $twig;
    protected $postrepo;

    public function __construct(Twig_Environment $twig, PostRepository $postrepo) {
        $this->twig = $twig;
        $this->postrepo = $postrepo;
    }

    public function indexAction(Request $request, Application $app) {
        return $this->twig->render('@PulpyAdmin/Posts/index.html.twig', array(
            'posts' => $this->postrepo->findAll()
        ));
    }
}