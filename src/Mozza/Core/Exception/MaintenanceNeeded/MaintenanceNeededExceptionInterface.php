<?php

namespace Mozza\Core\Exception\MaintenanceNeeded;

interface MaintenanceNeededExceptionInterface {
    public function setInformationalLabel($label);
    public function getInformationalLabel();
}