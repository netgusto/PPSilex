<?php

namespace Mozza\Core\Services;

use Mozza\Core\Entity\AbstractPost;

class PostResourceResolverService extends ResourceResolverService {

    public function filepathForPostAndResourceName(AbstractPost $post, $name) {
        return $this->filepathForResourceName($name);
    }

    public function relativeFilepathForPostAndResourceName(AbstractPost $post, $name) {
        
        $filepath = $this->filepathForPostAndResourceName($post, $name);
        if(!$filepath) {
            return null;
        }

        return $this->makeRelative($filepath);
    }
}