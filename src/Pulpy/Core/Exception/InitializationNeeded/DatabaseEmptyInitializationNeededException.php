<?php

namespace Pulpy\Core\Exception\InitializationNeeded;

class DatabaseEmptyInitializationNeededException
    extends \Exception
    implements
        InitializationNeededExceptionInterface {
}