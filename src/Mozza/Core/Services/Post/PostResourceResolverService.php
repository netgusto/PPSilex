<?php

namespace Mozza\Core\Services\Post;

use Mozza\Core\Entity\AbstractPost,
    Mozza\Core\Services\ResourceResolverService;

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