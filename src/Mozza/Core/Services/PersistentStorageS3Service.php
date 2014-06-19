<?php

namespace Mozza\Core\Services;

use Symfony\Component\Finder\Finder,
    Symfony\Component\Finder\SplFileInfo;

use Aws\S3\S3Client,
    Aws\Common\Credentials\Credentials as S3Credentials;

class PersistentStorageS3Service implements PersistentStorageServiceInterface {

    protected $client;
    protected $bucket;

    public function __construct(/* string */ $bucket, /* string */ $key, /* string */ $secret) {
        
        $this->bucket = $bucket;

        $this->client = S3Client::factory(array(
            'credentials' => new S3Credentials(
                $key,
                $secret
            )
        ));

        # Enables the s3:// stream wrapper
        $this->client->registerStreamWrapper();
    }

    public function getAll($dirpath='', $extension='') {

        $dirpath = rtrim($dirpath, '/');
        $streampath = 's3://' . $this->bucket . '/' . $dirpath;
        
        $finder = new Finder();
        $files = $finder->files()->in($streampath);

        if(trim($extension) !== '') {
            $files->name('*.' . ltrim($extension, '.'));
        }

        $items = array();
        foreach($files as $file) {
            $items[] = $this->getOne($dirpath . '/' . $file->getRelativePath() . '/' . $file->getRelativePathname());
        }

        reset($items);
        return $items;
    }

    public function getOne($relfilepath) {
        $relfilepath = ltrim($relfilepath, '/');
        $streampath = 's3://' . $this->bucket . '/' . $relfilepath;
        return new SplFileInfo(
            $streampath,
            dirname($relfilepath),
            basename($relfilepath)
        );
    }
}