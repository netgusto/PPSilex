<?php

namespace Mozza\Core\Exception\MaintenanceNeeded;

class DatabaseInvalidCredentialsMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}