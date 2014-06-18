<?php

namespace Mozza\Core\Services;

class SystemConfigService {

    protected $debug;
    protected $postsextension;
    protected $postsdir;
    protected $postswebresdir;
    protected $cachedb;

    public function __construct(array $systemconfig) {

        $this->debug = $systemconfig['debug'];
        $this->postsextension = $systemconfig['posts']['extension'];
        $this->postsdir = $systemconfig['posts']['dir'];
        $this->postswebresdir = $systemconfig['posts']['webresdir'];
    }

    public function getDebug() {
        return $this->debug;
    }

    public function getPostsextension() {
        return $this->postsextension;
    }

    public function getPostsdir() {
        return $this->postsdir;
    }

    public function getPostswebresdir() {
        return $this->postswebresdir;
    }
}