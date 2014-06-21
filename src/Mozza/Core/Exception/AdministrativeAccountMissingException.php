<?php

namespace Mozza\Core\Exception;

class AdministrativeAccountMissingException extends \Exception implements ApplicationNeedsMaintenanceExceptionInterface {
    use ApplicationNeedsMaintenanceExceptionTrait;
}