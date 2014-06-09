<?php

namespace Mozza\Core\Services;

use Symfony\Component\Yaml\Yaml;

use Mozza\Core\Entity\Post,
    Mozza\Core\Services\PostResolverService;

class PostReaderService {

    protected $postresolver;
    protected $timezone;

    public function __construct(PostResolverService $postresolver, \DateTimeZone $timezone) {
        $this->postresolver = $postresolver;
        $this->timezone = $timezone;
    }

    public function getPost($filepath) {

        if(!$this->postresolver->isFilepathLegit($filepath)) {
            return null;
        }

        if(!file_exists($filepath)) {
            return null;
        }

        # Obtaining the markdown
        $filepath = realpath($filepath);
        $markdown = file_get_contents($filepath);
        if(trim($markdown) === '') {
            return null;
        }

        # Splitting the YMF from the markdown source
        $postData = $this->splitFrontMatterFromSource($markdown);
        $postText = $this->splitIntroFromContent($postData['markdown']);

        $post = new Post();
        $post->setIntro($postText['intro']);
        $post->setContent($postText['content']);

        # Extract slug
        if(array_key_exists('slug', $postData['ymf'])) {
            $post->setSlug($postData['ymf']['slug']);
        } else {
            $fileinfo = pathinfo($filepath);
            $post->setSlug($fileinfo['filename']);  # without extension
        }

        # Extract title
        if(array_key_exists('title', $postData['ymf'])) {
            $post->setTitle($postData['ymf']['title']);
        } else {
            $post->setTitle('Untitled post');
        }

        # Extract author
        if(array_key_exists('author', $postData['ymf'])) {
            $post->setAuthor($postData['ymf']['author']);
        } else {
            $post->setAuthor('Anonymous');
        }

        # Extract twitter
        if(array_key_exists('twitter', $postData['ymf'])) {
            $post->setTwitter($postData['ymf']['twitter']);
        } else {
            $post->setTwitter(null);
        }

        # Extract date
        if(array_key_exists('date', $postData['ymf'])) {
            $dateString = $postData['ymf']['date'];
            $date = new \DateTime($dateString, $this->timezone);
            $post->setDate($date);
        } else {
            $post->setDate(null);
        }

        # Extract Status
        if(array_key_exists('status', $postData['ymf'])) {
            $post->setStatus($postData['ymf']['status']);
        } else {
            $post->setStatus('publish');
        }

        # Extract About
        if(array_key_exists('about', $postData['ymf'])) {
            $about = $postData['ymf']['about'];

            if(is_array($about)) {
                $post->setAbout($about);
            } elseif(is_string($about) && trim($about) !== '') {
                $post->setAbout(array($about));
            } else {
                $post->setAbout(array());
            }
        } else {
            $post->setAbout(array());
        }

        # Extract Metadata
        if(array_key_exists('meta', $postData['ymf'])) {
            $meta = $postData['ymf']['meta'];
            if(is_array($meta)) {
                $post->setMeta($meta);
            } else {
                $post->setMeta(array());
            }
        } else {
            $post->setMeta(array());
        }
        
        return $post;
    }

    protected function splitFrontMatterFromSource($markdown) {
        $res = array(
            'ymf' => null,
            'markdown' => null,
        );

        if(trim($markdown) === '') {
            # Content is empty
            return $res;
        }

        $matches = array();
        preg_match('%^---\n(?<ymf>.+?)\n---\n(?<markdown>.*)$%s', $markdown, $matches);

        if(empty($matches)) {
            # No YMF in the file
            return array(
                'ymf' => null,
                'markdown' => $markdown,
            );
        }

        $res['ymf'] = Yaml::parse($matches['ymf']);
        $res['markdown'] = $matches['markdown'];

        return $res;
    }

    protected function splitIntroFromContent($markdown) {
        $res = array(
            'intro' => null,
            'content' => null,
        );

        if(trim($markdown) === '') {
            # Content is empty
            return $res;
        }

        $matches = array();
        preg_match('%^(?<intro>.+?)\n(?<content>.*)$%s', $markdown, $matches);

        if(empty($matches)) {
            # No content in the file
            return array(
                'intro' => $markdown,
                'content' => null,
            );
        }

        $res['intro'] = trim($matches['intro']);
        $res['content'] = trim($matches['content']);

        return $res;
    }
}