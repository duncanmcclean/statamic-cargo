<?php

namespace Tests\Discounts\Eloquent;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Statamic;
use Tests\Stache\Query\DiscountQueryBuilderTest as StacheDiscountQueryBuilderTest;

class DiscountQueryBuilderTest extends StacheDiscountQueryBuilderTest
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.cargo.discounts', [
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\Cargo\Discounts\Eloquent\DiscountModel::class,
            'table' => 'cargo_discounts',
        ]);

        $this->app->bind('cargo.discounts.eloquent.model', function () {
            return config('statamic.cargo.discounts.model', \DuncanMcClean\Cargo\Discounts\Eloquent\DiscountModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Discounts\DiscountRepository::class,
            \DuncanMcClean\Cargo\Discounts\Eloquent\DiscountRepository::class
        );
    }
}
