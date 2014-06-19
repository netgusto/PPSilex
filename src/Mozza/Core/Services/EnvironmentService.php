<?php

namespace Mozza\Core\Services;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;

use josegonzalez\Dotenv;

class EnvironmentService {

    protected $env;
    protected $rootdir;
    protected $webdir;
    protected $srcdir;
    protected $appdir;
    protected $cachedir;
    protected $datadir;
    protected $themesdir;

    public function __construct($rootdir) {

        $envloader = new Dotenv\Loader($rootdir . '/.env');
        $this->env = $envloader->parse()->toArray();

        $this->rootdir = $rootdir;
        $this->webdir = $rootdir . '/web';
        $this->srcdir = $rootdir . '/src';
        $this->appdir = $rootdir . '/app';
        $this->cachedir = $this->appdir . '/cache';
        $this->themesdir = $this->webdir . '/vendor';

        $this->datadir = $rootdir . '/data';

        # Building a temporary root request to determine host url, as we cannot access the request service out of the scope of a controller
        $rootrequest = Request::createFromGlobals();
        $this->domain = $rootrequest->getHost();
        $this->siteurl = $rootrequest->getScheme() . '://' . $rootrequest->getHttpHost() . $rootrequest->getBaseUrl();
    }

    public function getEnv($what) {
        return array_key_exists($what, $this->env) ? $this->env[$what] : null;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getSiteurl() {
        return $this->siteurl;
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

    public function getThemesDir() {
        return $this->themesdir;
    }

    public function getDataDir() {
        return $this->datadir;
    }

    /*public function initialize(Application $app) {
        #
        # Culture
        #

        date_default_timezone_set($this->culture->getTimezone()->getName());
        setlocale(LC_ALL, $this->culture->getLocale());
    }*/
}