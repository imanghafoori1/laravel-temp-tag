<?php

use Imanghafoori\Tags\Services\TagService;

if (! function_exists('tempTags')) {
    function tempTags($model): TagService
    {
        $tagService = new TagService();
        $tagService->setModel($model);

        return $tagService;
    }
}
