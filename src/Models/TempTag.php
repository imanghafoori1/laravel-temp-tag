<?php

namespace Imanghafoori\Tags\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TempTag extends Model
{
//    use SoftDeletes;

    const UPDATED_AT = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'temp_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'payload',
        'expired_at',
        'taggable_type',
        'taggable_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'expired_at' => 'datetime',
        'deleted_at' => 'datetime',
        'payload' => 'json',
    ];

    public function taggable(): MorphTo
    {
        return $this->morphTo('taggable');
    }

    public function isActive()
    {
        return $this->expired_at->getTimestamp() > Carbon::now()->getTimestamp();
    }

    public function expiresAt(): Carbon
    {
        return $this->expired_at;
    }

    public function getPayload($key = null)
    {
        return ($key === null) ? $this->payload : ($this->payload[$key] ?? null);
    }

    /**
     * Determine if Temporary Tag is permanent.
     *
     * @return bool
     */
    public function isPermanent(): bool
    {
        return $this->expired_at->format('Y-m-d H:i:s') === '2038-01-01 00:00:00';
    }

    /**
     * Determine if Temporary Tag is temporary.
     *
     * @return bool
     */
    public function isTemporary(): bool
    {
        return ! $this->isPermanent();
    }

    public function scopeWhereTaggable(Builder $query, $taggable): Builder
    {
        return $query->where([
            'taggable_type' => $taggable->getMorphClass(),
            'taggable_id' => $taggable->getKey(),
        ]);
    }

    public function __toString()
    {
        return $this->getAttribute('title');
    }
}
