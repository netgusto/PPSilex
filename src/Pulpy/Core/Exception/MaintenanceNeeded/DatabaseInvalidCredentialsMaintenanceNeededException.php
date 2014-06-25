<?php

namespace Pulpy\Core\Exception\MaintenanceNeeded;

class DatabaseInvalidCredentialsMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}