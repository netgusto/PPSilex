<?php

namespace Pulpy\Core\Services;

use Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Filesystem\Exception\IOException;

class URLAbsolutizerService {

    protected $siteurl;
    
    public function __construct($siteurl, $webdir) {
        $this->siteurl = rtrim($siteurl, '/') . '/';
        $this->webdir = rtrim($webdir, '/') . '/';
    }

    public function absoluteSiteURL() {
        return $this->siteurl;
    }

    public function absoluteURLFromRoutePath($routepath) {
        return $this->absoluteURLFromRelativePath($routepath);
    }

    public function absoluteURLFromRelativePath($relpath) {
        return $this->siteurl . ltrim($relpath, '/');
    }

    public function absoluteURLFromAbsolutePath($abspath) {

        $filesystem = new Filesystem();
        try {
            $relpath = rtrim($filesystem->makePathRelative($abspath, $this->webdir), '/');
        } catch(IOException $e) {
            return null;
        }

        return $this->absoluteURLFromRelativePath($relpath);
    }
}