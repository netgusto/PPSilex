<?php

namespace Mozza\Core\Services;

class SiteConfigService {

    protected $title;
    protected $description;
    protected $theme;

    protected $ownername;
    protected $ownertwitter;
    protected $ownermail;
    protected $ownerwebsite;
    protected $ownerbio;

    protected $imagelogo;
    protected $imagecover;

    public function __construct(array $siteconfig) {

        $this->title = $siteconfig['title'];
        $this->description = $siteconfig['description'];
        $this->theme = $siteconfig['theme'];

        $this->ownername = $siteconfig['owner']['name'];
        $this->ownertwitter = $siteconfig['owner']['twitter'];
        $this->ownermail = $siteconfig['owner']['mail'];
        $this->ownerwebsite = $siteconfig['owner']['website'];
        $this->ownerbio = $siteconfig['owner']['bio'];

        $this->imagelogo = $siteconfig['images']['logo'];
        $this->imagecover = $siteconfig['images']['cover'];
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getTheme() {
        return $this->theme;
    }

    public function getOwnername() {
        return $this->ownername;
    }

    public function getOwnertwitter() {
        return $this->ownertwitter;
    }

    public function getOwnermail() {
        return $this->ownermail;
    }

    public function getOwnerwebsite() {
        return $this->ownerwebsite;
    }

    public function getOwnerbio() {
        return $this->ownerbio;
    }

    public function getImagelogo() {
        return $this->imagelogo;
    }

    public function getImagecover() {
        return $this->imagecover;
    }
}