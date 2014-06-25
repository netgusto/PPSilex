<?php

namespace Pulpy\Core\Services\CacheHandler;

class LastModifiedPostCacheHandlerService extends AbstractPostCacheHandlerService {
    
    public function cacheNeedsUpdate() {
        
        # Watching file changes
        # if configuration changes (like the file extension, for instance), you have to rebuild the cache manually (php console pulpy:cache:rebuild)

        # Note: Amazon AWS S3 does not support modification dates on folders, making this service incompatible with S3

        $postcachelastupdate = $this->systemstatus->getPostCacheLastUpdate();
        $postsdir = $this->fs->getOne($this->postspath);

        $lastmodified = $this->fs->getLastmodified($postsdir);
        $lastmodified->setTimezone($this->culture->getTimezone());

        return (is_null($postcachelastupdate) || ($lastmodified > $postcachelastupdate));
    }
}