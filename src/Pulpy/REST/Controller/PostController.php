<?php

namespace Pulpy\REST\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\JSONResponse,
    Symfony\Component\Security\Core\SecurityContext;

use Pulpy\Core\Repository\PostRepository,
    Pulpy\Core\Services\Post\PostSerializerService;

class PostController {

    public function __construct(PostRepository $postRepo, PostSerializerService $postserializer) {
        $this->postRepo = $postRepo;
        $this->postserializer = $postserializer;
    }

    public function indexAction(Request $request, Application $app) {
        
        $res = array();
        $posts = $this->postRepo->findAll();

        foreach($posts as $post) {
            $res[] = $this->postserializer->serialize($post);
        }
        
        return new JSONResponse($res);
    }
}