<?php

namespace Mozza\Core\Exception;

interface ApplicationNeedsMaintenanceExceptionInterface {
    public function setInformationalLabel($label);
    public function getInformationalLabel();
}