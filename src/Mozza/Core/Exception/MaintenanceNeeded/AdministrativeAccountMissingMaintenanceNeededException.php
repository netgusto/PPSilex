<?php

namespace Mozza\Core\Exception\MaintenanceNeeded;

class AdministrativeAccountMissingMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}