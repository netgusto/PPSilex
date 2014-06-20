<?php

namespace Mozza\Core\Services\PersistentStorage;

use Symfony\Component\Finder\SplFileInfo;

interface PersistentStorageServiceInterface {
    public function getAll($dirpath='', $extension='');
    public function getOne($filepath);
    public function exists(SplFileInfo $file);
    public function getLastModified(SplFileInfo $file);
    public function getContents(SplFileInfo $file);
    public function getUrl(SplFileInfo $file);
}