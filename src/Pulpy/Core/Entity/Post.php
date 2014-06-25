<?php

namespace Pulpy\Core\Entity;

/**
 * Post
 */
class Post extends AbstractPost
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
