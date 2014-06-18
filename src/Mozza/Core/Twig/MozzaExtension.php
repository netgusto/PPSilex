<?php

namespace Mozza\Core\Twig;

use Symfony\Component\Routing\Generator\UrlGenerator;

use Mozza\Core\Services\MarkdownProcessorInterface,
    Mozza\Core\Services\ResourceResolverService,
    Mozza\Core\Services\PostResourceResolverService,
    Mozza\Core\Services\URLAbsolutizerService,
    Mozza\Core\Services\PostURLGeneratorService,
    Mozza\Core\Services\PostSerializerService,
    Mozza\Core\Services\CultureService,
    Mozza\Core\Repository\PostRepository,
    Mozza\Core\Entity\Post;

class MozzaExtension extends \Twig_Extension {

    protected $postRepo;
    protected $postserializer;
    protected $urlgenerator;
    protected $posturlgenerator;
    protected $markdownProcessor;
    protected $resourceresolver;
    protected $postresourceresolver;
    protected $urlabsolutizer;
    protected $domainname;
    protected $culture;
    protected $appconfig;

    public function __construct(PostRepository $postRepo, PostSerializerService $postserializer, UrlGenerator $urlgenerator, PostURLGeneratorService $posturlgenerator, MarkdownProcessorInterface $markdownProcessor, ResourceResolverService $resourceresolver, PostResourceResolverService $postresourceresolver, URLAbsolutizerService $urlabsolutizer, $domainname, CultureService $culture, array $appconfig) {
        $this->postRepo = $postRepo;
        $this->postserializer = $postserializer;
        $this->urlgenerator = $urlgenerator;
        $this->posturlgenerator = $posturlgenerator;
        $this->markdownProcessor = $markdownProcessor;
        $this->resourceresolver = $resourceresolver;
        $this->postresourceresolver = $postresourceresolver;
        $this->urlabsolutizer = $urlabsolutizer;
        $this->domainname = $domainname;
        $this->culture = $culture;
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
            'topostresourceurl' => new \Twig_Filter_Method($this, 'topostresourceurl', array('is_safe' => array('html'))),
            'toabsoluteurl' => new \Twig_Filter_Method($this, 'toabsoluteurl', array('is_safe' => array('html'))),
            'serializepost' => new \Twig_Filter_Method($this, 'serializepost'),
            'humandate' => new \Twig_Filter_Method($this, 'humandate'),
        );
    }

    public function getFunctions() {
        return array(
            'component_disqus' => new \Twig_SimpleFunction('component_disqus', array($this, 'component_disqus'), array('is_safe' => array('html'))),
            'component_metatags' => new \Twig_SimpleFunction('component_metatags', array($this, 'component_metatags'), array('is_safe' => array('html'))),
            'component_googleanalytics' => new \Twig_SimpleFunction('component_googleanalytics', array($this, 'component_googleanalytics'), array('is_safe' => array('html'))),
            'posturl' => new \Twig_SimpleFunction('posturl', array($this, 'posturl'), array('is_safe' => array('html'))),
            'documenttitleforposttitle' => new \Twig_SimpleFunction('documenttitleforposttitle', array($this, 'documenttitleforposttitle')),
            'nextpost' => new \Twig_SimpleFunction('nextpost', array($this, 'nextpost')),
            'previouspost' => new \Twig_SimpleFunction('previouspost', array($this, 'previouspost')),
        );
    }

    public function serializepost(Post $post) {
        return $this->postserializer->serialize($post);
    }

    public function previouspost(Post $post) {
        return $this->postRepo->findPrevious($post);
    }

    public function nextpost(Post $post) {
        return $this->postRepo->findNext($post);
    }

    public function markdown($markdownsource) {
        return $this->markdownProcessor->toHtml($markdownsource);
    }

    public function inlineMarkdown($markdownsource) {
        return $this->markdownProcessor->toInlineHtml($markdownsource);
    }

    public function topostresourceurl($relfilepath, Post $post) {
        return $this->urlabsolutizer->absoluteURLFromRelativePath(
            $this->postresourceresolver->relativeFilepathForPostAndResourceName(
                $post,
                $relfilepath
            )
        );
    }

    public function toresourceurl($relfilepath) {
        return $this->urlabsolutizer->absoluteURLFromRelativePath(
            $this->resourceresolver->relativeFilepathForResourceName(
                $relfilepath
            )
        );
    }

    public function toabsoluteurl($relurl) {
        return $this->urlabsolutizer->absoluteURLFromRoutePath($relurl);
    }

    public function posturl($slug) {
        return $this->posturlgenerator->fromSlug($slug);
    }

    public function posturlabsolute($slug) {
        return $this->posturlgenerator->absolutefromSlug($slug);
    }

    public function humandate(\DateTime $date) {
        return $this->culture->humanDate($date);
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
        <!-- The disqus component -->
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
        <!-- /The disqus component -->
HTML;
        
        return $html;
    }

    public function component_googleanalytics() {

        if(
            !array_key_exists('components', $this->appconfig) ||
            !array_key_exists('googleanalytics', $this->appconfig['components']) ||
            !array_key_exists('uacode', $this->appconfig['components']['googleanalytics']) ||
            trim($this->appconfig['components']['googleanalytics']['uacode']) === ''
        ) {
            return '';
        }

        $uacode = trim($this->appconfig['components']['googleanalytics']['uacode']);
        $domainname = $this->domainname;

        # A custom domain name is set (for instance, to aggregate a subdomain and the main domain in GA)
        if(array_key_exists('domain', $this->appconfig['components']['googleanalytics'])) {

            $customdomainname = trim($this->appconfig['components']['googleanalytics']['domain']);
            $customdomainname = rtrim($customdomainname, '/');
            $customdomainname = trim($customdomainname);

            if(preg_match('%^https?://%i', $customdomainname)) {
                $domainparts = parse_url($customdomainname);
                $customdomainname = $domainparts['host'];
            }

            if(trim($customdomainname) !== '') {
                $domainname = $customdomainname;
            }
        }

        $jsuacode = json_encode($uacode);
        $jsdomainname = json_encode($domainname);

        $script =<<<SCRIPT
<!-- The google analytics component -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', {$jsuacode}, {$jsdomainname});
ga('send', 'pageview');

</script>
<!-- /The google analytics component -->
SCRIPT;
        
        return $script;
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

        $sitedescription = $this->cleanupMetaString($this->appconfig['site']['description']);
        $author = $this->cleanupMetaString($this->appconfig['site']['owner']['name']);
        $ownertwitter = $this->cleanupMetaString($this->appconfig['site']['owner']['twitter']);

        $metas['title'] = '<title>' . htmlspecialchars($this->documenttitleforposttitle('')) . '</title>';
        $metas['author'] = '<meta name="author" content="' . htmlspecialchars($author) . '">';
        $metas['description'] = '<meta name="description" content="' . htmlspecialchars($sitedescription) . '">';

        # RSS feed
        $rssRoutePath = $this->urlgenerator->generate('rss');
        $metas['rss'] = '<link rel="alternate" type="application/rss+xml" title="Subscribe using RSS" href="' . $this->urlabsolutizer->absoluteURLFromRoutePath($rssRoutePath) . '" />';

        return $metas;
    }

    public function documenttitleforposttitle(/*string*/ $posttitle = '') {
        $sitetitle = $this->cleanupMetaString($this->appconfig['site']['title']);
        $posttitle = $this->cleanupMetaString($posttitle);

        if(trim($posttitle) === '') {
            return $sitetitle;
        }

        return $posttitle . ' - ' . $sitetitle;
    }

    protected function cleanupMetaString($string) {
        return trim(strip_tags($string));
    }

    protected function metatagsWithPost(Post $post) {

        $metas = $this->metatagsWithoutPost();

        $sitetitle = $this->cleanupMetaString($this->appconfig['site']['title']);
        $sitedescription = $this->cleanupMetaString($this->appconfig['site']['description']);
        $posttitle = $this->cleanupMetaString($post->getTitle());
        $intro = $this->cleanupMetaString($this->markdown($post->getIntro()));
        $author = $this->cleanupMetaString($this->appconfig['site']['owner']['name']);
        $ownertwitter = $this->cleanupMetaString($this->appconfig['site']['owner']['twitter']);
        
        $imagerelpath = $post->getImage();
        if($imagerelpath) {
            $imageurl = $this->topostresourceurl($imagerelpath, $post);
        } else {
            $imageurl = null;
        }

        $canonicalurl =  $this->posturlabsolute($post->getSlug());

        $previouspost = $this->previouspost($post);
        $nextpost = $this->nextpost($post);

        $metas['title'] = '<title>' . htmlspecialchars($this->documenttitleforposttitle($posttitle)) . '</title>';
        $metas['author'] = '<meta name="author" content="' . htmlspecialchars($author) . '">';
        $metas['description'] = '<meta name="description" content="' . htmlspecialchars($sitedescription) . '">';
        $metas['link:canonical'] = '<link rel="canonical" href="' . $canonicalurl . '" />';
        
        if($previouspost) {
            $metas['link:prev'] = '<link rel="prev" href="' . $this->posturlabsolute($previouspost->getSlug()) . '" />';
        }

        if($nextpost) {
            $metas['link:next'] = '<link rel="next" href="' . $this->posturlabsolute($nextpost->getSlug()) . '" />';
        }

        
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