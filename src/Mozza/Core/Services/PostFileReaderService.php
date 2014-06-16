<?php

namespace Mozza\Core\Services;

use Symfony\Component\Yaml\Yaml;

use Mozza\Core\Entity\PostFile,
    Mozza\Core\Services\PostFileResolverService,
    Mozza\Core\Services\PostFingerprinterService;

class PostFileReaderService {

    protected $postresolver;
    protected $postresourceresolver;
    protected $postfingerprinter;
    protected $timezone;
    protected $appconfig;

    public function __construct(PostFileResolverService $postresolver, PostResourceResolverService $postresourceresolver, PostFingerprinterService $postfingerprinter, \DateTimeZone $timezone, array $appconfig) {
        $this->postresolver = $postresolver;
        $this->postresourceresolver = $postresourceresolver;
        $this->postfingerprinter = $postfingerprinter;
        $this->timezone = $timezone;
        $this->appconfig = $appconfig;
    }

    public function getPost($filepath) {

        if(!$this->postresolver->isFilepathLegit($filepath)) {
            return null;
        }

        if(!file_exists($filepath)) {
            return null;
        }

        $lastmodified = \DateTime::createFromFormat('U', filemtime($filepath));
        $lastmodified->setTimezone($this->timezone);

        # Obtaining the markdown
        $filepath = realpath($filepath);
        $markdown = file_get_contents($filepath);
        if(trim($markdown) === '') {
            return null;
        }

        # Splitting the YMF from the markdown source
        $postData = $this->splitFrontMatterFromSource($markdown);
        $postText = $this->splitIntroFromContent($postData['markdown']);

        $post = new PostFile();
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
            # Use the site author
            $post->setAuthor($this->appconfig['site']['owner']['name']);
        }

        # Extract website
        if(array_key_exists('website', $postData['ymf'])) {
            $post->setWebsite($postData['ymf']['website']);
        } else {
            # Use the site website
            $post->setWebsite($this->appconfig['site']['owner']['website']);
        }

        # Extract bio
        if(array_key_exists('bio', $postData['ymf'])) {
            $post->setBio($postData['ymf']['bio']);
        } else {
            # Use the site website
            $post->setBio($this->appconfig['site']['owner']['bio']);
        }

        # Extract twitter
        if(array_key_exists('twitter', $postData['ymf'])) {
            $post->setTwitter($postData['ymf']['twitter']);
        } else {
            # Use the site twitter
            $post->setTwitter($this->appconfig['site']['owner']['twitter']);
        }

        # Extract date
        if(array_key_exists('date', $postData['ymf'])) {
            $dateString = $postData['ymf']['date'];
            $date = new \DateTime($dateString, $this->timezone);
            $post->setDate($date);
        } else {
            # the file creation date
            $post->setDate($lastmodified);
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

        # Extract Comments (enabled or not)
        if(array_key_exists('comments', $postData['ymf'])) {
            $comments = mb_strtolower(trim($postData['ymf']['comments']), 'UTF-8');

            if(
                $comments === 'no' ||
                $comments === 'false' ||
                $comments === 'off'
            ) {
                $post->setComments(FALSE);
            } else {
                $post->setComments(TRUE);
            }
        } else {
            $post->setComments(TRUE);
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

        # Extract Image
        if(array_key_exists('image', $postData['ymf'])) {
            $imagerelpath = $postData['ymf']['image'];
            $imagepath = $this->postresourceresolver->filepathForPostAndResourceName($post, $imagerelpath);
            if($imagepath) {
                $post->setImage($imagerelpath); # As set in the post source, to be future-proof
            }
        } else {
            $post->setImage(null);
        }

        $post->setFingerprint(
            $this->postfingerprinter->fingerprint($post)
        );

        $post->setFilepath(
            $filepath
        );

        $post->setLastModified(
            $lastmodified
        );

        return $post;
    }

    protected function splitFrontMatterFromSource($markdown) {
        $res = array(
            'ymf' => array(),
            'markdown' => array(),
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
                'ymf' => array(),
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