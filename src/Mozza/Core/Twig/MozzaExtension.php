<?php

namespace Mozza\Core\Twig;

use Symfony\Component\Routing\Generator\UrlGenerator;

use Mozza\Core\Services\MarkdownProcessorInterface,
    Mozza\Core\Entity\Post;

class MozzaExtension extends \Twig_Extension {

    protected $urlgenerator;
    protected $markdownProcessor;
    protected $appconfig;

    public function __construct(UrlGenerator $urlgenerator, MarkdownProcessorInterface $markdownProcessor, array $appconfig) {
        $this->urlgenerator = $urlgenerator;
        $this->markdownProcessor = $markdownProcessor;
        $this->appconfig = $appconfig;
    }
    
    public function getName() {
        return 'mozza';
    }

    public function getFilters() {
        return array(
            'markdown' => new \Twig_Filter_Method($this, 'markdown', array('is_safe' => array('html'))),
            'inlinemarkdown' => new \Twig_Filter_Method($this, 'inlinemarkdown', array('is_safe' => array('html'))),
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

    public function component_disqus($options=array()) {

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

    protected function metatagsWithoutPost(Post $post) {
        $metas = array();

        $cleanup = function($string) {
            return trim(strip_tags($string));
        };

        $sitetitle = $cleanup($this->appconfig['site']['title']);
        $sitedescription = $cleanup($this->appconfig['site']['sitedescription']);
        $author = $cleanup($this->appconfig['site']['owner']['name']);
        $ownertwitter = $cleanup($this->appconfig['site']['owner']['twitter']);

        $metas[] = '<title>' . htmlspecialchars($sitetitle) . '</title>';
        $metas[] = '<meta name="author" content="' . htmlspecialchars($author) . '">';
        $metas[] = '<meta name="description" content="' . htmlspecialchars($sitedescription) . '">';

        return $metas;
    }

    protected function metatagsWithPost(Post $post) {
        $metas = array();

        $cleanup = function($string) {
            return trim(strip_tags($string));
        };

        $sitetitle = $cleanup($this->appconfig['site']['title']);
        $sitedescription = $cleanup($this->appconfig['site']['description']);
        $posttitle = $cleanup($post->getTitle());
        $intro = $cleanup($this->markdown($post->getIntro()));
        $author = $cleanup($this->appconfig['site']['owner']['name']);
        $ownertwitter = $cleanup($this->appconfig['site']['owner']['twitter']);

        $metas[] = '<title>' . htmlspecialchars($posttitle . ' - ' . $sitetitle) . '</title>';
        $metas[] = '<meta name="author" content="' . htmlspecialchars($author) . '">';
        $metas[] = '<meta name="description" content="' . htmlspecialchars($sitedescription) . '">';
        $metas[] = '<link rel="canonical" href="' . $this->urlgenerator->generate('post', array('slug' => $post->getSlug())) . '" />';
        
        # Twitter card
        $metas[] = '<meta name="twitter:card" content="summary">';
        $metas[] = '<meta name="twitter:site" content="' . htmlspecialchars($sitetitle) . '">';
        $metas[] = '<meta name="twitter:title" content="' . htmlspecialchars($posttitle) . '">';
        $metas[] = '<meta name="twitter:description" content="' . htmlspecialchars($intro) . '">';
        $metas[] = '<meta name="twitter:creator" content="' . htmlspecialchars($ownertwitter) . '">';
        $metas[] = '<meta name="twitter:image:src" content="">';

        # OpenGraph
        $metas[] = '<meta property="og:type" content="article">';
        $metas[] = '<meta property="og:title" content="' . htmlspecialchars($posttitle) . '">';
        $metas[] = '<meta property="og:site_name" content="' . htmlspecialchars($sitetitle) . '">';
        $metas[] = '<meta property="og:description" content="' . htmlspecialchars($intro) . '">';
        $metas[] = '<meta property="og:image" content="">';

        return $metas;
    }
}