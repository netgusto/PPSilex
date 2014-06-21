<?php

namespace Mozza\Core\Exception;

class DatabaseEmptyException extends \Exception
    implements
        ApplicationNeedsMaintenanceExceptionInterface,
        InitializationTriggeringExceptionInterface {

    use ApplicationNeedsMaintenanceExceptionTrait;
}