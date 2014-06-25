<?php

namespace Pulpy\Core\Exception\MaintenanceNeeded;

interface MaintenanceNeededExceptionInterface {
    public function setInformationalLabel($label);
    public function getInformationalLabel();
}