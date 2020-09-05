<?php

namespace Imanghafoori\Tags\Traits;

use Illuminate\Support\Carbon;
use Imanghafoori\Tags\Models\TempTag;

trait hasTempTags
{
    public function tempTags()
    {
        return $this->morphMany(TempTag::class, 'temp_taggable', 'taggable_type', 'taggable_id', 'id');
    }

    public function activeTempTags()
    {
        return $this->tempTags()->where('expired_at', '>=', Carbon::now());
    }

    public function expiredTempTags()
    {
        return $this->tempTags()->where('expired_at', '<', Carbon::now());
    }
}
