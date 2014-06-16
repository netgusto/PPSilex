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

        $filepath = trim($this->truepath($filepath));
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

    # Taken from http://stackoverflow.com/a/4050444
    /**
     * This function is to replace PHP's extremely buggy realpath().
     * @param string The original path, can be relative etc.
     * @return string The resolved path, it might not exist.
     */
    protected function truepath($path){
        // whether $path is unix or not
        $unipath=strlen($path)==0 || $path{0}!='/';
        // attempts to detect if path is relative in which case, add cwd
        if(strpos($path,':')===false && $unipath)
            $path=getcwd().DIRECTORY_SEPARATOR.$path;
        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.'  == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $path=implode(DIRECTORY_SEPARATOR, $absolutes);
        // resolve any symlinks
        if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
        // put initial separator that could have been lost
        $path=!$unipath ? '/'.$path : $path;
        return $path;
    }
}