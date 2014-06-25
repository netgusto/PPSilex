<?php

namespace Pulpy\Core\Services\Post;

use Pulpy\Core\Entity\AbstractPost,
    Pulpy\Core\Services\ResourceResolverService;

class PostResourceResolverService extends ResourceResolverService {

    public function fileForPostAndResourceName(AbstractPost $post, $name) {
        return $this->fileForResourceName($name);
    }

    public function urlForPostAndResourceName(AbstractPost $post, $name) {
        
        $file = $this->fileForPostAndResourceName($post, $name);
        if(is_null($file)) {
            return null;
        }

        return $this->fs->getUrl($file);
    }
}