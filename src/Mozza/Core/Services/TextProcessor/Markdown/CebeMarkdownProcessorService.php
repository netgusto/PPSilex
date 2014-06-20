<?php

namespace Mozza\Core\Services\TextProcessor\Markdown;

use cebe\markdown\GithubMarkdown as CebeMarkdown;

class CebeMarkdownProcessorService implements MarkdownProcessorInterface {
    
    public function __construct() {
        $this->processor = new CebeMarkdown();
    }

    public function toHtml($markdown) {
        return $this->processor->parse($markdown);
    }

    public function toInlineHtml($markdown) {
        return $this->processor->parseParagraph($markdown);
    }
}