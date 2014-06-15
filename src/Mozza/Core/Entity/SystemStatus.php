<?php

namespace Mozza\Core\Entity;

class SystemStatus {
    
    /**
     * @var integer
     */
    protected $id;

    protected $postcachelastupdate;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set postcachestatus
     *
     * @param string $title
     * @return AbstractPost
     */
    public function setPostcachelastupdate($postcachelastupdate)
    {
        $this->postcachelastupdate = $postcachelastupdate;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getPostcachelastupdate()
    {
        return $this->postcachelastupdate;
    }
}
