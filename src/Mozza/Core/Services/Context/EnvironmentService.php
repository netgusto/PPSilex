<?php

namespace Mozza\Core\Services\Context;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;

use josegonzalez\Dotenv;

class EnvironmentService {

    protected $env;
    protected $scalarinterpreter;
    protected $rootdir;
    protected $webdir;
    protected $srcdir;
    protected $appdir;
    protected $cachedir;
    protected $datadir;
    protected $themesdir;

    protected $initializationmode;

    public function __construct(array $env, $scalarinterpreter, $rootdir) {

        $this->env = $env;
        $this->scalarinterpreter = $scalarinterpreter;
        $this->rootdir = $rootdir;
        $this->webdir = $rootdir . '/web';
        $this->srcdir = $rootdir . '/src';
        $this->appdir = $rootdir . '/app';
        $this->cachedir = $this->appdir . '/cache';
        $this->themesdir = $this->webdir . '/vendor';

        # Building a temporary root request to determine host url, as we cannot access the request service out of the scope of a controller
        $rootrequest = Request::createFromGlobals();
        $this->domain = $rootrequest->getHost();
        $this->scheme = $rootrequest->getScheme();
        $this->siteurl = $this->scheme . '://' . $rootrequest->getHttpHost() . $rootrequest->getBaseUrl();

        $this->debug = $this->scalarinterpreter->toBooleanDefaultFalse($this->getEnv('DEBUG'));
        $this->initializationmode = FALSE;
    }

    public function getEnv($what) {
        return array_key_exists($what, $this->env) ? $this->env[$what] : null;
    }

    public function getDebug() {
        return $this->debug;
    }

    public function getInitializationMode() {
        return $this->initializationmode;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getScheme() {
        return $this->scheme;
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

    ###########################################################################
    # Setter for maintenance mode
    ###########################################################################
    
    public function setInitializationmode($initializationmode) {
        if(!is_bool($initializationmode)) {
            throw new \InvalidArgumentException('EnvironmentService::setInitializationmode() expects parameter 1 to be boolean.');
        }

        $this->initializationmode = $initializationmode;
        return $this;
    }
}