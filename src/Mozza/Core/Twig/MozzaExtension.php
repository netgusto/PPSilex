<?php

namespace Mozza\Core\Twig;

use Symfony\Component\Routing\Generator\UrlGenerator;

use Mozza\Core\Services\MarkdownProcessorInterface,
    Mozza\Core\Services\PostResourceResolverService,
    Mozza\Core\Services\URLAbsolutizerService,
    Mozza\Core\Entity\Post;

class MozzaExtension extends \Twig_Extension {

    protected $urlgenerator;
    protected $markdownProcessor;
    protected $postresourceresolver;
    protected $urlabsolutizer;
    protected $appconfig;

    public function __construct(UrlGenerator $urlgenerator, MarkdownProcessorInterface $markdownProcessor, PostResourceResolverService $postresourceresolver, URLAbsolutizerService $urlabsolutizer, array $appconfig) {
        $this->urlgenerator = $urlgenerator;
        $this->markdownProcessor = $markdownProcessor;
        $this->postresourceresolver = $postresourceresolver;
        $this->urlabsolutizer = $urlabsolutizer;
        $this->appconfig = $appconfig;
    }
    
    public function getName() {
        return 'mozza';
    }

    public function getFilters() {
        return array(
            'markdown' => new \Twig_Filter_Method($this, 'markdown', array('is_safe' => array('html'))),
            'inlinemarkdown' => new \Twig_Filter_Method($this, 'inlinemarkdown', array('is_safe' => array('html'))),
            'toresourceurl' => new \Twig_Filter_Method($this, 'toresourceurl', array('is_safe' => array('html'))),
        );
    }

    public function getFunctions() {
        return array(
            'component_disqus' => new \Twig_SimpleFunction('component_disqus', array($this, 'component_disqus'), array('is_safe' => array('html'))),
            'component_metatags' => new \Twig_SimpleFunction('component_metatags', array($this, 'component_metatags'), array('is_safe' => array('html'))),
        );
    }

    public function markdown($markdownsource) {
        return $this->markdownProcessor->toHtml($markdownsource);
    }

    public function inlineMarkdown($markdownsource) {
        return $this->markdownProcessor->toInlineHtml($markdownsource);
    }

    public function toresourceurl($relfilepath, Post $post) {
        return $this->urlabsolutizer->absoluteURLFromRelativePath(
            $this->postresourceresolver->relativeFilepathForPostAndResourceName(
                $post,
                $relfilepath
            )
        );
    }

    public function component_disqus(Post $post) {

        if(!$post->getComments()) {
            return '';
        }

        if(
            !array_key_exists('components', $this->appconfig) ||
            !array_key_exists('disqus', $this->appconfig['components']) ||
            !array_key_exists('shortname', $this->appconfig['components']['disqus']) ||
            trim($this->appconfig['components']['disqus']['shortname']) === ''
        ) {
            return '';
        }

        $shortname = trim($this->appconfig['components']['disqus']['shortname']);

        $html =<<<HTML
        <div id="disqus_thread"></div>
        <script type="text/javascript">
            /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
            var disqus_shortname = '{$shortname}'; // required: replace example with your forum shortname

            /* * * DON'T EDIT BELOW THIS LINE * * */
            (function() {
                var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
            })();
        </script>
        <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
HTML;
        
        return $html;
    }

    public function component_metatags(Post $post = null) {

        if(is_null($post)) {
            $metas = $this->metatagsWithoutPost();
        } else {
            # post
            $metas = $this->metatagsWithPost($post);
        }

        return implode("\n", $metas);
    }

    protected function metatagsWithoutPost() {
        $metas = array();

        $cleanup = function($string) {
            return trim(strip_tags($string));
        };

        $sitetitle = $cleanup($this->appconfig['site']['title']);
        $sitedescription = $cleanup($this->appconfig['site']['description']);
        $author = $cleanup($this->appconfig['site']['owner']['name']);
        $ownertwitter = $cleanup($this->appconfig['site']['owner']['twitter']);

        $metas['title'] = '<title>' . htmlspecialchars($sitetitle) . '</title>';
        $metas['author'] = '<meta name="author" content="' . htmlspecialchars($author) . '">';
        $metas['description'] = '<meta name="description" content="' . htmlspecialchars($sitedescription) . '">';

        # RSS feed
        $rssRoutePath = $this->urlgenerator->generate('feed');
        $metas['rss'] = '<link rel="alternate" type="application/rss+xml" title="Subscribe using RSS" href="' . $this->urlabsolutizer->absoluteURLFromRoutePath($rssRoutePath) . '" />';

        return $metas;
    }

    protected function metatagsWithPost(Post $post) {

        $cleanup = function($string) {
            return trim(strip_tags($string));
        };

        $metas = $this->metatagsWithoutPost();

        $sitetitle = $cleanup($this->appconfig['site']['title']);
        $sitedescription = $cleanup($this->appconfig['site']['description']);
        $posttitle = $cleanup($post->getTitle());
        $intro = $cleanup($this->markdown($post->getIntro()));
        $author = $cleanup($this->appconfig['site']['owner']['name']);
        $ownertwitter = $cleanup($this->appconfig['site']['owner']['twitter']);
        
        $imagerelpath = $post->getImage();
        if($imagerelpath) {
            $imageurl = $this->toresourceurl($imagerelpath, $post);
        } else {
            $imageurl = null;
        }

        $canonicalurl =  $this->urlabsolutizer->absoluteURLFromRoutePath(
            $this->urlgenerator->generate('post', array('slug' => $post->getSlug()))
        );

        $metas['title'] = '<title>' . htmlspecialchars($posttitle . ' - ' . $sitetitle) . '</title>';
        $metas['author'] = '<meta name="author" content="' . htmlspecialchars($author) . '">';
        $metas['description'] = '<meta name="description" content="' . htmlspecialchars($sitedescription) . '">';
        $metas['canonical'] = '<link rel="canonical" href="' . $canonicalurl . '" />';
        
        # Twitter card
        $metas['twitter:card'] = '<meta name="twitter:card" content="summary">';
        $metas['twitter:site'] = '<meta name="twitter:site" content="' . htmlspecialchars($sitetitle) . '">';
        $metas['twitter:title'] = '<meta name="twitter:title" content="' . htmlspecialchars($posttitle) . '">';
        $metas['twitter:description'] = '<meta name="twitter:description" content="' . htmlspecialchars($intro) . '">';
        $metas['twitter:creator'] = '<meta name="twitter:creator" content="' . htmlspecialchars($ownertwitter) . '">';

        if($imageurl) {
            $metas['twitter:image:src'] = '<meta name="twitter:image:src" content="' . htmlspecialchars($imageurl) . '">';
        }

        # OpenGraph
        $metas['og:type'] = '<meta property="og:type" content="article">';
        $metas['og:title'] = '<meta property="og:title" content="' . htmlspecialchars($posttitle) . '">';
        $metas['og:site_name'] = '<meta property="og:site_name" content="' . htmlspecialchars($sitetitle) . '">';
        $metas['og:description'] = '<meta property="og:description" content="' . htmlspecialchars($intro) . '">';
        if($imageurl) {
            $metas['og:image'] = '<meta property="og:image" content="' . htmlspecialchars($imageurl) . '">';
        }

        return $metas;
    }
}