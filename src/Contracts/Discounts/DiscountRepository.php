<?php

namespace DuncanMcClean\Cargo\Contracts\Discounts;

use Statamic\Fields\Blueprint;

interface DiscountRepository
{
    public function all();

    public function query();

    public function find($handle): ?Discount;

    public function findOrFail($handle): Discount;

    public function findByDiscountCode(string $code): ?Discount;

    public function make(): Discount;

    public function save(Discount $discount): void;

    public function delete(Discount $discount): void;

    public function blueprint(): Blueprint;

    public static function bindings(): array;
}
