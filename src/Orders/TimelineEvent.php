<?php

namespace DuncanMcClean\Cargo\Orders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;

class TimelineEvent implements Arrayable
{
    public function __construct(
        protected int $timestamp,
        protected string $type,
        protected $user,
        protected array $metadata = []
    ) {}

    public static function make(array $data): self
    {
        return new static(
            timestamp: $data['timestamp'],
            type: $data['type'],
            user: $data['user'] ?? null,
            metadata: $data['metadata'] ?? []
        );
    }

    public function timestamp(): Carbon
    {
        return Carbon::createFromTimestamp($this->timestamp);
    }

    public function type(): TimelineEventType
    {
        $timelineEventTypes = app('statamic.extensions')[TimelineEventType::class];

        if (! $timelineEventTypes->has($this->type)) {
            throw new \Exception("Timeline Event Type [{$this->type}] does not exist.");
        }

        return app($timelineEventTypes->get($this->type))->setTimelineEvent($this);
    }

    public function user(): ?UserContract
    {
        if (! $this->user) {
            return null;
        }

        return User::find($this->user);
    }

    public function metadata(?string $key = null)
    {
        if (func_num_args() === 0) {
            return collect($this->metadata);
        }

        return $this->metadata[$key] ?? null;
    }

    public function message(): string
    {
        return $this->type()->message();
    }

    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'type' => $this->type,
            'user' => $this->user,
            'metadata' => $this->metadata,
        ];
    }
}
