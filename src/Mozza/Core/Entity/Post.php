<?php

namespace Mozza\Core\Entity;

class Post {

    protected $slug;

    protected $title;
    protected $author;
    protected $twitter;
    protected $date;
    protected $status;
    protected $intro;
    protected $content;
    protected $image;
    protected $comments = TRUE;
    protected $about = array();
    protected $meta = array();

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
        return $this;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
        return $this;
    }

    public function getTwitter() {
        return $this->twitter;
    }

    public function setTwitter($twitter) {
        $this->twitter = $twitter;
        return $this;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate(\DateTime $date) {
        $this->date = $date;
        return $this;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getIntro() {
        return $this->intro;
    }

    public function setIntro($intro) {
        $this->intro = $intro;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function getAbout() {
        return $this->about;
    }

    public function setAbout(array $about) {
        $this->about = $about;
        return $this;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage($imagepath) {
        $this->image = $imagepath;
        return $this;
    }

    public function getComments() {
        return $this->comments;
    }

    public function setComments($comments) {
        $this->comments = $comments;
        return $this;
    }

    public function getMeta() {
        return $this->meta;
    }

    public function setMeta(array $meta) {
        $this->meta = $meta;
        return $this;
    }
}