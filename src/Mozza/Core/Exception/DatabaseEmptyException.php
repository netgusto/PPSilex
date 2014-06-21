<?php

namespace Mozza\Core\Exception;

class DatabaseEmptyException extends \Exception implements ApplicationNeedsMaintenanceExceptionInterface {
    use ApplicationNeedsMaintenanceExceptionTrait;
}