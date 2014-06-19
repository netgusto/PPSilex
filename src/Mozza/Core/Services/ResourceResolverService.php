<?php

namespace Mozza\Core\Services;

use Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Filesystem\Exception\IOException;

class ResourceResolverService {
    
    protected $webpath;
    protected $resourcespath;
    
    public function __construct($webpath, $resourcespath) {
        $this->webpath = rtrim($webpath, '/') . '/';
        $this->resourcespath = rtrim($resourcespath, '/') . '/';
    }

    public function filepathForResourceName($name) {
        
        $filepath = $this->resourcespath . $name;

        if(!$this->isFilepathLegit($filepath)) {
            return null;
        }

        return $filepath;
    }

    public function relativeFilepathForResourceName($name) {
        
        $filepath = $this->filepathForResourceName($name);

        if(!$filepath) {
            return null;
        }

        return $this->makeRelative($filepath);
    }

    public function isFilepathLegit($filepath) {

        $filepath = trim($filepath);
        if($filepath === '') {
            return FALSE;
        }

        if(mb_strlen($filepath, 'UTF-8') <= mb_strlen($this->resourcespath, 'UTF-8')) {
            return FALSE;
        }

        if(substr($filepath, 0, mb_strlen($this->resourcespath, 'UTF-8')) !== $this->resourcespath) {
            return FALSE;
        }

        $pathinfo = pathinfo($filepath);
        return (trim($pathinfo['filename']) !== '');
    }

    protected function makeRelative($filepath) {
        
        $filesystem = new Filesystem();
        try {
            $relpath = rtrim($filesystem->makePathRelative($filepath, $this->webpath), '/');
        } catch(IOException $e) {
            return null;
        }

        return $relpath;
    }
}