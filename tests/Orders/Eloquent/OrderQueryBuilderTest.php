<?php

namespace Tests\Orders\Eloquent;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Statamic;
use Tests\Stache\Query\OrderQueryBuilderTest as StacheOrderQueryBuilderTest;

class OrderQueryBuilderTest extends StacheOrderQueryBuilderTest
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.cargo.orders', [
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class,
            'table' => 'orders',
        ]);

        $this->app->bind('cargo.orders.eloquent.model', function () {
            return config('statamic.cargo.orders.model', \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class);
        });

        $this->app->bind('cargo.orders.eloquent.line_items_model', function () {
            return config('statamic.cargo.orders.line_items_model', \DuncanMcClean\Cargo\Orders\Eloquent\LineItemModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Orders\OrderRepository::class,
            \DuncanMcClean\Cargo\Orders\Eloquent\OrderRepository::class
        );
    }
}