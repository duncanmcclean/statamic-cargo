<?php

namespace DuncanMcClean\Cargo\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Exceptions\JsonResourceException;

class Resource
{
    const array CARGO_RESOURCES = [
        CartResource::class,
    ];

    public static function map(array $resources): void
    {
        collect($resources)
            ->each(fn ($class, $bindable) => static::validateBinding($bindable, $class))
            ->each(fn ($class, $bindable) => app()->bind($bindable, fn () => $class));
    }

    public static function mapDefaults(): void
    {
        $resources = collect(static::CARGO_RESOURCES)
            ->reject(fn ($resource) => app()->has($resource))
            ->keyBy(fn ($resource) => $resource)
            ->all();

        static::map($resources);
    }

    private static function validateBinding(string $bindable, string $class): void
    {
        if (! in_array($bindable, static::CARGO_RESOURCES)) {
            throw new JsonResourceException("[{$bindable}] is not a valid Cargo API resource");
        }

        if (! is_subclass_of($class, JsonResource::class)) {
            throw new JsonResourceException("[{$class}] must be a subclass of ".JsonResource::class);
        }
    }
}
