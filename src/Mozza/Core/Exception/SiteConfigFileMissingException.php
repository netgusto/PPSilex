<?php

namespace Mozza\Core\Exception;

class SiteConfigFileMissingException extends \Exception implements ApplicationNeedsMaintenanceExceptionInterface {

    use ApplicationNeedsMaintenanceExceptionTrait;

    protected $filepath;
    
    public function setFilePath() {
        $this->filepath = $filepath;
        return $this;
    }

    public function getFilePath() {
        return $this->filepath;
    }
}