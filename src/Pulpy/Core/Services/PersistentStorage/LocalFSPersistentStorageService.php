<?php

namespace Pulpy\Core\Services\PersistentStorage;

use Symfony\Component\Finder\Finder,
    Symfony\Component\Finder\SplFileInfo;

class LocalFSPersistentStorageService implements PersistentStorageServiceInterface {

    protected $siteurl;
    protected $absbasedir;

    public function __construct($absbasedir = '', /* string */ $siteurl) {
        $this->absbasedir = '/' . trim($absbasedir, '/');
        $this->siteurl = rtrim($siteurl, '/');
    }

    public function getAll($dirpath='', $extension='') {

        $dirpath = trim($dirpath, '/');
        $streampath = 'file://' . $this->absbasedir . '/' . $dirpath;
        
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
        $filepath = $this->absbasedir . '/' . $relfilepath;

        $streampath = 'file://' . $filepath;
        return new SplFileInfo(
            $streampath,
            dirname($relfilepath),
            basename($relfilepath)
        );
    }

    public function exists(SplFileInfo $file) {
        return file_exists($file);
    }

    public function getLastModified(SplFileInfo $file) {
        return \DateTime::createFromFormat('U', filemtime($file->getPathName()));
    }

    public function getContents(SplFileInfo $file) {
        return file_get_contents($file->getPathName());
    }

    public function getUrl(SplFileInfo $file) {
        return $this->siteurl . '/' . $file->getRelativePath() . '/' . $file->getRelativePathname();
    }
}