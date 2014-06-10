<?php

namespace Mozza\Core\Services;

use Symfony\Component\Routing\Generator\UrlGenerator;

use Mozza\Core\Repository\PostRepository,
    Mozza\Core\Services\MarkdownProcessorInterface,
    Mozza\Core\Services\URLAbsolutizerService,
    Mozza\Core\Services\PostResourceResolverService,
    Mozza\Core\Exception;

class PostSerializerService {

    protected $postRepo;
    protected $markdownprocessor;
    protected $urlgenerator;
    protected $urlabsolutizer;
    protected $postresourceresolver;
    protected $appconfig;
    
    public function __construct(PostRepository $postRepo, MarkdownProcessorInterface $markdownprocessor, UrlGenerator $urlgenerator, URLAbsolutizerService $urlabsolutizer, PostResourceResolverService $postresourceresolver, array $appconfig) {
        $this->postRepo = $postRepo;
        $this->markdownprocessor = $markdownprocessor;
        $this->urlgenerator = $urlgenerator;
        $this->urlabsolutizer = $urlabsolutizer;
        $this->postresourceresolver = $postresourceresolver;
        $this->appconfig = $appconfig;
    }

    public function serialize($post) {
        $postintro = trim($this->markdownprocessor->toInlineHtml($post->getIntro()));
        $postcontent = trim($this->markdownprocessor->toHtml($post->getContent()));

        $url = $this->urlabsolutizer->absoluteURLFromRoutePath($this->urlgenerator->generate('post', array('slug' => $post->getSlug())));

        $imagerelpath = $post->getImage();
        if($imagerelpath) {
            $imageurl = $this->urlabsolutizer->absoluteURLFromRelativePath(
                $this->postresourceresolver->relativeFilepathForPostAndResourceName(
                    $post,
                    $imagerelpath
                )
            );
        } else {
            $imageurl = null;
        }

        $previouspost = $this->postRepo->findPrevious($post);
        $nextpost = $this->postRepo->findNext($post);

        return array(
            'url' => $url,
            'slug' => $post->getSlug(),
            'image' => $imageurl,
            'title' => trim($post->getTitle()),
            'intro' => $postintro,
            'content' => $postcontent,
            'author' => $post->getAuthor(),
            'twitter' => $post->getTwitter(),
            'about' => $post->getAbout(),
            'date_human' => $post->getDate()->format($this->appconfig['date']['format']),
            'date_iso' => $post->getDate()->format('c'),    # ISO 8601; equivalent to (new Date()).toJSON() in javascript
            'comments' => $post->getComments(), # true / false
            'next_slug' => $nextpost ? $nextpost->getSlug() : null,
            'previous_slug' => $previouspost ? $previouspost->getSlug() : null,
        );
    }
}