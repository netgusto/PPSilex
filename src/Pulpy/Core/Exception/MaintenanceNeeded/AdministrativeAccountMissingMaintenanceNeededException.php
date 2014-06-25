<?php

namespace Pulpy\Core\Exception\MaintenanceNeeded;

class AdministrativeAccountMissingMaintenanceNeededException
    extends \Exception
    implements MaintenanceNeededExceptionInterface {

    use MaintenanceNeededExceptionTrait;
}