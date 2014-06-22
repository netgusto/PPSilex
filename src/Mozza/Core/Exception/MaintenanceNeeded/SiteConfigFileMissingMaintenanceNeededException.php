<?php

namespace Mozza\Core\Exception\MaintenanceNeeded;

class SiteConfigFileMissingMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;

    protected $filepath;
    
    public function setFilePath() {
        $this->filepath = $filepath;
        return $this;
    }

    public function getFilePath() {
        return $this->filepath;
    }
}