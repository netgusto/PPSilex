<?php

namespace Mozza\Core\Services;

class ScalarInterpreterService {

    public function __construct() {
    }

    public function toBooleanDefaultFalse($value) {

        if(in_array(
            strtolower($value),
            array(TRUE, 'true', 'on', 'yes'),
            TRUE    # strict comparison
        )) {
            return TRUE;
        }

        return FALSE;
    }

    public function toBooleanDefaultTrue($value) {

        if(in_array(
            strtolower($value),
            array(FALSE, 'false', 'off', 'no'),
            TRUE    # strict comparison
        )) {
            return FALSE;
        }

        return TRUE;
    }
}