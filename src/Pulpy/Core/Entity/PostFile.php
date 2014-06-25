<?php

namespace Pulpy\Core\Entity;

/**
 * Post
 */
class PostFile extends AbstractPost {

    /**
     * @var string
     */
    protected $filepath;

    /**
     * Set filepath
     *
     * @param string $filepath
     * @return AbstractPost
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get filepath
     *
     * @return array 
     */
    public function getFilepath()
    {
        return $this->filepath;
    }
}
