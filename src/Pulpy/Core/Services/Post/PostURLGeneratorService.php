<?php

namespace Pulpy\Core\Services\Post;

use Symfony\Component\Routing\Generator\UrlGenerator;

use Pulpy\Entity\Post,
    Pulpy\Core\Repository\PostRepository,
    Pulpy\Core\Services\URLAbsolutizerService,
    Pulpy\Core\Exception;

class PostURLGeneratorService {

    protected $postRepo;
    protected $urlgenerator;
    protected $urlabsolutizer;
    
    public function __construct(PostRepository $postRepo, UrlGenerator $urlgenerator, URLAbsolutizerService $urlabsolutizer) {
        $this->postRepo = $postRepo;
        $this->urlgenerator = $urlgenerator;
        $this->urlabsolutizer = $urlabsolutizer;
    }

    public function fromSlug($slug) {
        return $this->urlgenerator->generate('post', array(
            'slug' => $slug
        ));
    }

    public function absoluteFromSlug($slug) {
        return $this->urlabsolutizer->absoluteURLFromRoutePath(
            $this->fromSlug($slug)
        );
    }

    public function fromPost(Post $post) {
        return $this->fromSlug($post->getSlug());
    }

    public function absoluteFromPost(Post $post) {
        return $this->absoluteFromSlug($post->getSlug());
    }
}