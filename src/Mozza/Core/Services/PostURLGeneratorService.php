<?php

namespace Mozza\Core\Services;

use Symfony\Component\Routing\Generator\UrlGenerator;

use Mozza\Entity\Post,
    Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\URLAbsolutizerService,
    Mozza\Core\Exception;

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