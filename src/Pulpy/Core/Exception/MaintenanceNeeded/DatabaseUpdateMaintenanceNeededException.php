<?php

namespace Pulpy\Core\Exception\MaintenanceNeeded;

class DatabaseUpdateMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}