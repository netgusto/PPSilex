<?php

namespace Mozza\Core\Services;

use Silex\Application;

use Habitat\Habitat,
    josegonzalez\Dotenv;

class EnvironmentService {

    protected $env;

    public function __construct($rootdir) {
        $envloader = new Dotenv\Loader($rootdir . '/.env');
        $this->env = $envloader->parse()->toArray();

        $this->rootdir = $rootdir;
        $this->webdir = $rootdir . '/web';
        $this->srcdir = $rootdir . '/src';
        $this->appdir = $rootdir . '/app';
        $this->cachedir = $this->appdir . '/cache';
    }

    public function getEnv($what) {
        return array_key_exists($what, $this->env) ? $this->env[$what] : null;
    }

    public function getRootDir() {
        return $this->rootdir;
    }

    public function getWebDir() {
        return $this->webdir;
    }

    public function getSrcDir() {
        return $this->srcdir;
    }

    public function getAppDir() {
        return $this->appdir;
    }

    public function getCacheDir() {
        return $this->cachedir;
    }

    /*public function initialize(Application $app) {
        #
        # Culture
        #

        date_default_timezone_set($this->culture->getTimezone()->getName());
        setlocale(LC_ALL, $this->culture->getLocale());
    }*/
}