<?php

namespace Mozza\Core\Exception;

class DatabaseInvalidCredentialsException extends \Exception implements ApplicationNeedsMaintenanceExceptionInterface {
    use ApplicationNeedsMaintenanceExceptionTrait;
}