<?php

namespace Tests\Cart\Eloquent;

use DuncanMcClean\Cargo\Cart\Eloquent\CartModel;
use DuncanMcClean\Cargo\Cart\Eloquent\CartRepository;
use DuncanMcClean\Cargo\Contracts\Cart\QueryBuilder;
use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Stache\Stache;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartRepositoryTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk, RefreshDatabase;

    protected $repo;

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

        $this->repo = $this->app->make(\DuncanMcClean\Cargo\Contracts\Cart\CartRepository::class);

        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();
    }

    #[Test]
    public function can_find_carts()
    {
        $model = CartModel::create([
            'site' => 'default',
            'customer' => json_encode(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']),
            'grand_total' => 2500,
            'sub_total' => 2500,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'data' => ['foo' => 'bar'],
        ]);

        $model->lineItems()->create([
            'id' => '123',
            'product' => 'abc',
            'quantity' => 1,
            'unit_price' => 2500,
            'sub_total' => 2500,
            'tax_total' => 0,
            'total' => 2500,
        ]);

        $cart = $this->repo->find($model->id);

        $this->assertEquals($model->id, $cart->id());
        $this->assertEquals($model->site, $cart->site()->handle());
        $this->assertEquals(json_decode($model->customer, true)['name'], $cart->customer->name());
        $this->assertEquals(json_decode($model->customer, true)['email'], $cart->customer->email());
        $this->assertEquals($model->grand_total, $cart->grandTotal());
        $this->assertEquals($model->sub_total, $cart->subTotal());
        $this->assertEquals($model->discount_total, $cart->discountTotal());
        $this->assertEquals($model->tax_total, $cart->taxTotal());
        $this->assertEquals($model->shipping_total, $cart->shippingTotal());
        $this->assertEquals($model->data, $cart->data()->except('updated_at')->all());

        $this->assertEquals('123', $cart->lineItems()->first()->id());
        $this->assertEquals(2500, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function can_save_a_cart()
    {
        $cart = Cart::make()
            ->site('default')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0)
            ->lineItems([['id' => '123', 'product' => 'abc', 'quantity' => 1, 'total' => 2500]])
            ->data($data = ['foo' => 'bar']);

        $this->repo->save($cart);

        $this->assertDatabaseHas('carts', [
            'site' => 'default',
            'grand_total' => 2500,
            'data->foo' => 'bar',
        ]);

        $this->assertDatabaseHas('cart_line_items', [
            'cart_id' => $cart->id(),
            'product' => 'abc',
            'quantity' => 1,
            'total' => 2500,
        ]);

        $this->assertNotNull($cart->id());
    }

    #[Test]
    public function can_delete_a_cart()
    {
        $model = CartModel::create([
            'id' => '123',
            'site' => 'default',
            'customer' => json_encode(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']),
            'grand_total' => 0,
            'sub_total' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
        ]);

        $cart = $this->repo->find($model->id);

        $this->repo->delete($cart);

        $this->assertDatabaseMissing('orders', [
            'id' => '123',
            'site' => 'default',
        ]);
    }
}
