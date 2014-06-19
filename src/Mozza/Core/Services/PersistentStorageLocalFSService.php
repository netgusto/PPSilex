<?php

namespace Mozza\Core\Services;

use Symfony\Component\Finder\Finder,
    Symfony\Component\Finder\SplFileInfo;

class PersistentStorageLocalFSService implements PersistentStorageServiceInterface {

    protected $basepath;
    public function __construct($basepath = '') {
        $this->basepath = '/' . trim($basepath, '/');
    }

    public function getAll($dirpath='', $extension='') {

        $dirpath = trim($dirpath, '/');
        $streampath = 'file://' . $this->basepath . '/' . $dirpath;
        
        $finder = new Finder();
        $files = $finder->files()->in($streampath);

        if(trim($extension) !== '') {
            $files->name('*.' . ltrim($extension, '.'));
        }

        $items = array();
        foreach($files as $file) {
            
            $relfilepath = $file->getRelativePath();
            $relfilepathname = $file->getRelativePathname();

            $items[] = $this->getOne($dirpath . '/' . ($relfilepath !== '' ? $relfilepath . '/' : '') . $relfilepathname);
        }

        reset($items);
        return $items;
    }

    public function getOne($relfilepath) {

        $relfilepath = ltrim($relfilepath, '/');
        $filepath = $this->basepath . '/' . $relfilepath;

        $streampath = 'file://' . $this->bucket . $filepath;
        return new SplFileInfo(
            $streampath,
            dirname($relfilepath),
            basename($relfilepath)
        );
    }
}