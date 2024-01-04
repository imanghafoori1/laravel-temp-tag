<?php

namespace Imanghafoori\Tags\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class TempTag extends Model
{
    private static $_dateFormat = 'Y-m-d H:i:s';

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

    public static function boot()
    {
        parent::boot();

        self::deleted(function ($model) {
            cache()->store('temp_tag')->delete($model->getCacheKey());
        });
    }

    public function getCacheKey()
    {
        return 'temp_tag:'.$this->taggable_type.$this->taggable_id.','.$this->title;
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

    public function scopeExpired($q)
    {
        return $q->where('expired_at', '<', $this->now());
    }

    public function scopeActive($q)
    {
        return $q->where('expired_at', '>', $this->now());
    }

    public function __toString()
    {
        return $this->getAttribute('title');
    }

    private function now(): string
    {
        return Carbon::now()->format(self::$_dateFormat);
    }

    public function incrementPayload($key, $amount = 1)
    {
        try {
            $this->increment('payload->'.$key, $amount);
        } catch (\Throwable $e) {
            // laravel does not fully support incrementing json values.
        }
    }

    public function getAttribute($key)
    {
        if (! is_null($this->getPayload($key))) {
            return $this->getPayload($key);
        }

        return parent::getAttribute($key);
    }
}
