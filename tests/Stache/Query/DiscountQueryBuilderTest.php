<?php

namespace Stache\Query;

use DuncanMcClean\Cargo\Facades\Discount;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class DiscountQueryBuilderTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

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
