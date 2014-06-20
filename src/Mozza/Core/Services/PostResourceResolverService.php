<?php

namespace Mozza\Core\Services;

use Mozza\Core\Entity\AbstractPost;

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