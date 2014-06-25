<?php

namespace Pulpy\Core\Exception\MaintenanceNeeded;

class SystemStatusMissingMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}