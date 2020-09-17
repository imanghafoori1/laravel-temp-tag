<?php

namespace Imanghafoori\Tags\Services;

use Illuminate\Support\Carbon;
use Imanghafoori\Tags\Models\TempTag;

class TagService
{
    private static $maxLifeTime = '2038-01-01 00:00:00';

    private static $dateFormat = 'Y-m-d H:i:s';

    private $model;

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getActiveTag(string $tag)
    {
        return $this->getTagQuery($tag)->where('expired_at', '>', $this->now())->first() ?: null;
    }

    public function getTag(string $tag): ?TempTag
    {
        return $this->getTagQuery($tag)->first();
    }

    public function getAllExpiredTags()
    {
        return $this->query()->where('expired_at', '<', $this->now())->get();
    }

    public function getAllActiveTags()
    {
        return $this->query()->where('expired_at', '>', $this->now())->get();
    }

    public function getAllTags()
    {
        return $this->query()->get();
    }

    public function getTagsExpireAfter(Carbon $date)
    {
        return $this->query()->where('expired_at', '>', $date->format(self::$dateFormat))->get();
    }

    public function getTagsExpireBefore(Carbon $date)
    {
        return $this->query()->where('expired_at', '<', $date->format(self::$dateFormat))->get();
    }

    public function getExpiredTag(string $tag)
    {
        return $this->getTagQuery($tag)->where('expired_at', '<', $this->now())->first();
    }

    public function isPermanent(string $tag)
    {
        return $this->getTagQuery($tag)->where('expired_at', '<', self::$maxLifeTime)->first() ?: false;
    }

    public function tagIt($tag, $carbon = null, $payload = null, $eventName = null)
    {
        $data = $this->getTaggableWhere();
        $exp = $this->expireDate($carbon);

        $new_tags = [];
        foreach ((array)$tag as $tg) {
            $data['title'] = $tg;
            $new_tags[] = $tagObj = TempTag::query()->updateOrCreate($data, $data + $exp + ['payload' => $payload]);
            $this->fireEvent($eventName, $tagObj);
        }

        return $new_tags;
    }

    public function unTag($titles = null)
    {
        $forTaggable = $this->getTaggableWhere();

        $tags = TempTag::query()->where($forTaggable);

        $titles && $tags->whereIn('title', (array)$titles);
        $tags = $tags->get();
        $this->deleteAll($tags);
    }

    public function deleteExpiredTags()
    {
        $tags = TempTag::query()
            ->where('expired_at', '<=', $this->now())
            ->get();

        $this->deleteAll($tags);
    }

    private function getTaggableWhere()
    {
        $taggable = $this->model;
        return [
            'taggable_id' => $taggable->getKey(),
            'taggable_type' => $taggable->getTable()
        ];
    }

    private function query()
    {
        return TempTag::query()->where($this->getTaggableWhere());
    }

    private function now(): string
    {
        return Carbon::now()->format(self::$dateFormat);
    }

    private function fireEvent($event, $tag)
    {
        ! $event && $event = 'tmp_tagged';

        $event .= ':'.$this->model->getTable().','.$tag->title;

        event($event, [$this->model, $tag]);
    }

    private function expireDate($carbon)
    {
        $carbon = $carbon ? $carbon->format(self::$dateFormat) : self::$maxLifeTime;

        return ['expired_at' => $carbon];
    }

    public function expireNow($tag)
    {
        $this->tagIt($tag, Carbon::now()->subSeconds(1), 'tmp_tag_expired');
    }

    private function deleteAll($tags)
    {
        $tags->each(function ($tag) {
            $tag->delete();
        });
    }

    private function getTagQuery(string $tag)
    {
        return $this->query()->where('title', $tag);
    }
}
