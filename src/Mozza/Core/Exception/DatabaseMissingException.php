<?php

namespace Mozza\Core\Exception;

class DatabaseMissingException extends \Exception implements ApplicationNeedsMaintenanceExceptionInterface {
    use ApplicationNeedsMaintenanceExceptionTrait;
}