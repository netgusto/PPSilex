<?php

namespace Pulpy\Core\Services\Context;

class DotEnvFileReaderService {

    public function __construct() {
    }

    public function read($abspath) {
        
        $res = array();

        if(!is_file($abspath)) {
            throw new \Exception('Environment file not found.');
        }

        if(!is_readable($abspath)) {
            throw new \Exception('Environment file is not readable.');
        }

        # This part borrowed and adapted from https://github.com/vlucas/phpdotenv/blob/master/src/Dotenv.php

        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($abspath, FILE_SKIP_EMPTY_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        foreach($lines as $line) {
            
            # Only use non-empty lines that look like setters
            if(strpos($line, '=') === FALSE) {
                continue;
            }

            # Strip quotes because putenv can't handle them. Also remove 'export' if present
            $line = trim(str_replace(array('export ', '\'', '"'), '', $line));

            # Skip comments
            if(empty($line) || in_array($line{0}, array(';', '#'))) {
                continue;
            }

            # Remove whitespaces around key & value
            list($key, $val) = array_map('trim', explode('=', $line, 2));

            $res[$key] = $val;
        }

        reset($res);
        return $res;
    }
}