<?php

namespace Tests\Stache\Query;

use DuncanMcClean\Cargo\Facades\Discount;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class DiscountQueryBuilderTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function can_query_columns()
    {
        Discount::make()->handle('a')->type('percentage_off')->set('percentage_off', 10)->save();
        Discount::make()->handle('b')->type('amount_off')->set('percentage_off', 1500)->save();
        Discount::make()->handle('c')->type('percentage_off')->set('percentage_off', 15)->save();

        $query = Discount::query()->where('type', 'percentage_off')->get();

        $this->assertCount(2, $query);
        $this->assertEquals(['a', 'c'], $query->map->id()->all());
    }

    #[Test]
    public function sorting_by_unsafe_method_does_not_invoke_it()
    {
        Discount::make()->handle('abc')->save();
        Discount::make()->handle('def')->save();
        Discount::make()->handle('ghi')->save();

        $count = Discount::all()->count();
        $this->assertGreaterThan(0, $count);

        Discount::query()->orderBy('delete', 'asc')->get();

        $this->assertCount($count, Discount::all());
    }
}
