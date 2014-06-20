<?php

namespace Mozza\Core\Entity;

class SystemStatus {
    
    /**
     * @var integer
     */
    protected $id;

    protected $postcachelastupdate;

    protected $configuredversion;

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
     * Set postcachelastupdate
     *
     * @param string $title
     * @return SystemStatus
     */
    public function setPostcachelastupdate($postcachelastupdate)
    {
        $this->postcachelastupdate = $postcachelastupdate;

        return $this;
    }

    /**
     * Get postcachelastupdate
     *
     * @return string 
     */
    public function getPostcachelastupdate()
    {
        return $this->postcachelastupdate;
    }

    /**
     * Set configuredversion
     *
     * @param string $configuredversion
     * @return SystemStatus
     */
    public function setConfiguredversion($configuredversion)
    {
        $this->configuredversion = $configuredversion;

        return $this;
    }

    /**
     * Get configuredversion
     *
     * @return string 
     */
    public function getConfiguredversion()
    {
        return $this->configuredversion;
    }
}
