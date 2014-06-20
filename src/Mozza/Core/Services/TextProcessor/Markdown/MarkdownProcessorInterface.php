<?php

namespace Mozza\Core\Services\TextProcessor\Markdown;

interface MarkdownProcessorInterface {
    public function toHtml($markdown);
    public function toInlineHtml($markdown);
}