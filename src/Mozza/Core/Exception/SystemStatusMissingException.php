<?php

namespace Mozza\Core\Exception;

class SystemStatusMissingException extends \Exception implements ApplicationNeedsMaintenanceExceptionInterface {
    use ApplicationNeedsMaintenanceExceptionTrait;
}