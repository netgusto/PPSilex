<?php

namespace Mozza\Core\Services;

use Symfony\Component\Finder\Finder;

use Mozza\Core\Services\PostFileResolverService,
    Mozza\Core\Services\PostFileReaderService,
    Mozza\Core\Entity\Post;

class PostFileRepositoryService {
    protected $postresolver;
    protected $postpath;
    protected $postfileextension;
    protected $runtimecache;
    
    public function __construct(PostFileResolverService $postresolver, PostFileReaderService $postreader, $postspath, $postfileextension) {
        $this->postresolver = $postresolver;
        $this->postreader = $postreader;
        $this->postspath = rtrim($postspath, '/') . '/';
        $this->postfileextension = ltrim($postfileextension, '.');
    }

    public function findOneBySlug($slug) {

        $postpath = $this->postresolver->filepathFromSlug($slug);
        $post = $this->postreader->getPost($postpath);
        if($post) {
            return $post;
        }

        # post is not found based on slug
        # We list all posts, and try to find the one with the correct slug (could be defined in it's Yaml Front matter)

        $posts = $this->findAll();
        foreach($posts as $post) {
            if($post->getSlug() === $slug) {

                # It's a match
                return $post;
            }
        }

        return null;
    }

    public function findPrevious(Post $post) {
        $current = null;
        $posts = $this->findAll();

        for($k = 0; $k < count($posts); $k++) {
            if($post->getSlug() === $posts[$k]->getSlug()) {
                $current = $k;
                break;
            }
        }

        if(
            is_null($current) ||
            $current + 1 >= count($posts)
        ) {
            return null;
        }

        return $posts[$current+1];
    }

    public function findNext(Post $post) {

        $current = null;
        $posts = $this->findAll();

        for($k = 0; $k < count($posts); $k++) {
            if($post->getSlug() === $posts[$k]->getSlug()) {
                $current = $k;
                break;
            }
        }

        if(
            is_null($current) ||
            $current - 1 < 0
        ) {
            return null;
        }

        return $posts[$current-1];
    }

    public function findAll() {

        $posts = array();

        $finder = new Finder();
        $files = $finder->files()->in($this->postspath)->name('*.' . $this->postfileextension);
        foreach($files as $file) {
            $post = $this->postreader->getPost($file->getRealpath());
            if($post->getStatus() === 'publish') {
                $posts[] = $post;
            }
        }

        usort($posts, function($a, $b) {
            return $a->getDate() < $b->getDate();
        });

        return $posts;
    }
}