<?php

namespace Pulpy\Core\Services\Config;

class SiteConfigService {

    protected $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function getTitle() {
        return $this->config['title'];
    }

    public function getDescription() {
        return $this->config['description'];
    }

    public function getTheme() {
        return $this->config['theme'];
    }

    public function getOwnername() {
        return $this->config['owner']['name'];
    }

    public function getOwnertwitter() {
        return $this->config['owner']['twitter'];
    }

    public function getOwnermail() {
        return $this->config['owner']['mail'];
    }

    public function getOwnerwebsite() {
        return $this->config['owner']['website'];
    }

    public function getOwnerbio() {
        return $this->config['owner']['bio'];
    }

    public function getImagelogo() {
        return $this->config['images']['logo'];
    }

    public function getImagecover() {
        return $this->config['images']['cover'];
    }

    public function getHomepostsperpage() {
        return $this->config['home']['postsperpage'];
    }

    public function getCultureLocale() {
        return $this->config['culture']['locale'];
    }

    public function getCulturedatetimezone() {
        return $this->config['culture']['date']['timezone'];
    }

    public function getCulturedateformat() {
        return $this->config['culture']['date']['format'];
    }

    public function getPostsdir() {
        return ltrim($this->config['posts']['dir'], '/');   # relative fo fs service root
    }

    public function getPostsextension() {
        return $this->config['posts']['extension'];
    }

    public function getResourcesdir() {
        return $this->config['resources']['dir'];
    }

    public function getComponentsGoogleanalyticsUacode() {
        return $this->config['components']['googleanalytics']['uacode'];
    }

    public function getComponentsGoogleanalyticsDomain() {
        return array_key_exists('domain', $this->config['components']['googleanalytics']) ? $this->config['components']['googleanalytics']['domain'] : null;
    }

    public function getComponentsDisqusShortname() {
        return $this->config['components']['disqus']['shortname'];
    }
}