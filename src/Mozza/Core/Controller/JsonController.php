<?php

namespace Mozza\Core\Controller;

use Silex\Application,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\JsonResponse;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\PostSerializerService,
    Mozza\Core\Exception;

class JsonController {

    protected $postRepo;
    protected $postserializer;

    public function __construct(PostRepository $postRepo, PostSerializerService $postserializer) {
        $this->postRepo = $postRepo;
        $this->postserializer = $postserializer;
    }

    public function indexAction(Request $request, Application $app) {

        $res = array();

        # Export all posts
        $posts = $this->postRepo->findAll();
        foreach($posts as $post) {
            $res[] = $this->postserializer->serialize($post);
        }

        $response = new JsonResponse($res);

        return $response;
    }

    public function postAction(Request $request, Application $app, $slug) {
        $post = $this->postRepo->findBySlug($slug);
        if(!$post) {
            throw new Exception\PostNotFoundException('Post with slug ' . $slug . ' does not exist.');
        }

        return new JsonResponse($this->postserializer->serialize($post, $app));
    }
}