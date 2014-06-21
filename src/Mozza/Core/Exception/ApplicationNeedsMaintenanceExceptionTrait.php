<?php

namespace Mozza\Core\Exception;

trait ApplicationNeedsMaintenanceExceptionTrait {
    
    protected $label;

    public function setInformationalLabel($label) {
        $this->label = $label;
        return $this;
    }

    public function getInformationalLabel() {
        return $this->label;
    }
}