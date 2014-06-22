<?php

namespace Mozza\Core\Exception\InitializationNeeded;

class DatabaseMissingInitializationNeededException
    extends \Exception
    implements
        InitializationNeededExceptionInterface {
}