<?php

namespace Mozza\Core\Services;

use Symfony\Component\Finder\SplFileInfo;

interface PersistentStorageServiceInterface {
    public function getAll($dirpath='', $extension='');
    public function getOne($filepath);
}