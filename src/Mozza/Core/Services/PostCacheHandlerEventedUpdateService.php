<?php

namespace Mozza\Core\Services;

class PostCacheHandlerEventedUpdateService extends AbstractPostCacheHandlerService {
    
    public function cacheNeedsUpdate() {
        # This service is not able to determine if cache needs to be updated on it's own
        # The cache updated has to be triggered by an event (a manuel user request) on the system
        
        return FALSE;
    }
}