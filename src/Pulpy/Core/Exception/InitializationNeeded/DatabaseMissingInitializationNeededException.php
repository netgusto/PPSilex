<?php

namespace Pulpy\Core\Exception\InitializationNeeded;

class DatabaseMissingInitializationNeededException
    extends \Exception
    implements
        InitializationNeededExceptionInterface {
}