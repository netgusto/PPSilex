<?php

namespace Mozza\Core\Exception\InitializationNeeded;

class DatabaseEmptyInitializationNeededException
    extends \Exception
    implements
        InitializationNeededExceptionInterface {
}