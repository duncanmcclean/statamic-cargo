<?php

namespace DuncanMcClean\Cargo\Orders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Statamic\Facades\User;

class TimelineEvent implements Arrayable
{
    protected int $timestamp;

    protected string $event;

    protected ?string $user = null;

    protected array $metadata = [];

    public function __construct(array $data)
    {
        $this->timestamp = $data['timestamp'];
        $this->event = $data['event'];
        $this->user = $data['user'] ?? null;
        $this->metadata = $data['metadata'] ?? [];
    }

    public static function make(array $data): self
    {
        return new static($data);
    }

    public function timestamp(): int
    {
        return $this->timestamp;
    }

    public function date(): Carbon
    {
        return Carbon::createFromTimestamp($this->timestamp);
    }

    public function event(): string
    {
        return $this->event;
    }

    public function user(): ?string
    {
        return $this->user;
    }

    public function userObject()
    {
        if (! $this->user) {
            return null;
        }

        return User::find($this->user);
    }

    public function metadata(?string $key = null)
    {
        if ($key === null) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }

    public function toArray(): array
    {
        $data = [
            'timestamp' => $this->timestamp,
            'event' => $this->event,
        ];

        if ($this->user) {
            $data['user'] = $this->user;
        }

        if (! empty($this->metadata)) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }
}
