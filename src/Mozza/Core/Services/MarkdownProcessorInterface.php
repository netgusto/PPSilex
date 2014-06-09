<?php

namespace Mozza\Core\Services;

interface MarkdownProcessorInterface {
    public function toHtml($markdown);
    public function toInlineHtml($markdown);
}