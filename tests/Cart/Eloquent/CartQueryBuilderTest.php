<?php

namespace Tests\Cart\Eloquent;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Statamic;
use Tests\Stache\Query\CartQueryBuilderTest as StacheCartQueryBuilderTest;

class CartQueryBuilderTest extends StacheCartQueryBuilderTest
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.cargo.carts', [
            ...config('statamic.cargo.carts'),
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\Cargo\Cart\Eloquent\CartModel::class,
            'table' => 'carts',
        ]);

        $this->app->bind('cargo.carts.eloquent.model', function () {
            return config('statamic.cargo.carts.model', \DuncanMcClean\Cargo\Cart\Eloquent\CartModel::class);
        });

        $this->app->bind('cargo.carts.eloquent.line_items_model', function () {
            return config('statamic.cargo.carts.line_items_model', \DuncanMcClean\Cargo\Cart\Eloquent\LineItemModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Cart\CartRepository::class,
            \DuncanMcClean\Cargo\Cart\Eloquent\CartRepository::class
        );

        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();
    }
}
