<?php

namespace Mozza\Core\Services;

class PostFileResolverService {

    protected $postspath;
    protected $postfileextension;
    
    public function __construct($postspath, $postfileextension) {
        $this->postspath = rtrim($postspath, '/') . '/';
        $this->postfileextension = ltrim($postfileextension, '.');
    }

    public function filepathFromUrl($url) {
        $urlinfo = parse_url($url);
        return $this->filepathFromSlug($urlinfo['path']);
    }

    public function filepathFromSlug($slug) {
        return $this->postspath . $this->filenameFromSlug($slug);
    }

    public function filepathFromFilename($filename) {
        return $this->postspath . $filename;
    }

    public function filenameFromUrl($url) {
        $urlinfo = parse_url($url);
        return $this->filenameFromSlug($urlinfo['path']);
    }

    public function filenameFromSlug($slug) {
        return $slug . '.' . $this->postfileextension;
    }

    public function isFilepathLegit($filepath) {

        $filepath = trim($filepath);

        if($filepath === '') {
            return FALSE;
        }

        if(mb_strlen($filepath, 'UTF-8') <= mb_strlen($this->postspath, 'UTF-8')) {
            return FALSE;
        }

        if(substr($filepath, 0, mb_strlen($this->postspath, 'UTF-8')) !== $this->postspath) {
            return FALSE;
        }

        $pathinfo = pathinfo($filepath);
        return (
            trim($pathinfo['filename']) !== '' &&
            $pathinfo['extension'] === $this->postfileextension
        );
    }

    public function toUrl($postname) {
        return '/' . $postname;
    }
}