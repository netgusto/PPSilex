<?php

namespace Mozza\Core\Entity;

/**
 * Post
 */
abstract class AbstractPost {

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var string
     */
    protected $twitter;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $intro;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $image;

    /**
     * @var boolean
     */
    protected $comments = TRUE;

    /**
     * @var array
     */
    protected $about = array();

    /**
     * @var array
     */
    protected $meta = array();

    /**
     * @var string
     */
    protected $fingerprint;

    /**
     * @var \DateTime
     */
    protected $lastmodified;

    /**
     * Set slug
     *
     * @param string $slug
     * @return AbstractPost
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return AbstractPost
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set author
     *
     * @param string $author
     * @return AbstractPost
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return AbstractPost
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string 
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return AbstractPost
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return AbstractPost
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set intro
     *
     * @param string $intro
     * @return AbstractPost
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * Get intro
     *
     * @return string 
     */
    public function getIntro()
    {
        return $this->intro;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return AbstractPost
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return AbstractPost
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set comments
     *
     * @param boolean $comments
     * @return AbstractPost
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return boolean
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set about
     *
     * @param array $about
     * @return AbstractPost
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return array 
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set meta
     *
     * @param array $meta
     * @return AbstractPost
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get meta
     *
     * @return array 
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set fingerprint
     *
     * @param string $fingerprint
     * @return AbstractPost
     */
    public function setFingerprint($fingerprint)
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    /**
     * Get fingerprint
     *
     * @return array 
     */
    public function getFingerprint()
    {
        return $this->fingerprint;
    }

    /**
     * Set lastmodified
     *
     * @param \DateTime $lastmodified
     * @return AbstractPost
     */
    public function setLastmodified(\DateTime $lastmodified)
    {
        $this->lastmodified = $lastmodified;

        return $this;
    }

    /**
     * Get lastmodified
     *
     * @return \DateTime 
     */
    public function getLastmodified()
    {
        return $this->lastmodified;
    }
}
