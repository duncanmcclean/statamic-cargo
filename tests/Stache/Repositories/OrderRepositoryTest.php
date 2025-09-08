<?php

namespace Tests\Stache\Repositories;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\YAML;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();

        $this->repo = $this->app->make(\DuncanMcClean\Cargo\Contracts\Orders\OrderRepository::class);
    }

    #[Test]
    public function can_find_orders()
    {
        Order::make()
            ->id('abc')
            ->site('default')
            ->orderNumber(1234)
            ->date(Carbon::parse('2025-01-01 12:00:00'))
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0)
            ->data(['foo' => 'bar'])
            ->lineItems([[
                'id' => '123',
                'product' => 'abc',
                'quantity' => 1,
                'unit_price' => 2500,
                'sub_total' => 2500,
                'tax_total' => 0,
                'total' => 2500,
            ]])
            ->save();

        $order = $this->repo->find('abc');

        $this->assertEquals('abc', $order->id());
        $this->assertEquals('default', $order->site()->handle());
        $this->assertEquals(1234, $order->orderNumber());
        $this->assertEquals(Carbon::parse('2025-01-01 12:00:00'), $order->date());
        $this->assertEquals('CJ Cregg', $order->customer()->name());
        $this->assertEquals('cj.cregg@whitehouse.gov', $order->customer()->email());
        $this->assertEquals(2500, $order->grandTotal());
        $this->assertEquals(2500, $order->subTotal());
        $this->assertEquals(0, $order->discountTotal());
        $this->assertEquals(0, $order->taxTotal());
        $this->assertEquals(0, $order->shippingTotal());
        $this->assertEquals(['foo' => 'bar'], $order->data()->except('updated_at', 'fingerprint')->all());

        $this->assertEquals('123', $order->lineItems()->first()->id());
        $this->assertEquals(2500, $order->lineItems()->first()->total());
    }

    #[Test]
    public function can_make_order_from_cart()
    {
        $cart = Cart::make()
            ->id('abc')
            ->lineItems([
                [
                    'product' => '123',
                    'quantity' => 1,
                    'total' => 2500,
                ],
            ])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->set('foo', 'bar')
            ->set('baz', 'foobar');

        $order = $this->repo->makeFromCart($cart);

        $this->assertInstanceOf(OrderContract::class, $order);

        $this->assertEquals($cart->lineItems(), $order->lineItems());
        $this->assertEquals(2500, $order->grandTotal());
        $this->assertEquals(2500, $order->subTotal());
        $this->assertEquals(0, $order->discountTotal());
        $this->assertEquals(0, $order->taxTotal());
        $this->assertEquals(0, $order->shippingTotal());
        $this->assertEquals('bar', $order->get('foo'));
        $this->assertEquals('foobar', $order->get('baz'));
    }

    #[Test]
    public function can_save_an_order()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();

        $order = Order::make()
            ->id('abc')
            ->site('default')
            ->orderNumber(1234)
            ->date(Carbon::parse('2025-01-01 12:00:00'))
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0)
            ->lineItems([['id' => '123', 'product' => 'abc', 'quantity' => 1, 'total' => 2500]])
            ->data($data = ['foo' => 'bar']);

        $this->repo->save($order);

        $this->assertStringContainsString('content/cargo/orders/2025-01-01-1200.1234.yaml', $order->path());

        $yaml = YAML::file($order->path())->parse();

        $this->assertEquals('abc', $yaml['id']);
        $this->assertEquals(2500, $yaml['grand_total']);
        $this->assertEquals('bar', $yaml['foo']);

        $this->assertEquals([
            'id' => '123',
            'product' => 'abc',
            'quantity' => 1,
            'total' => 2500,
        ], $yaml['line_items'][0]);
    }

    #[Test]
    public function can_generate_order_number()
    {
        Order::make()->orderNumber(1000)->save();
        Order::make()->orderNumber(1001)->save();
        Order::make()->orderNumber(1002)->save();

        $order = Order::make();

        $this->repo->save($order);

        $this->assertEquals(1003, $order->orderNumber());
    }

    #[Test]
    public function can_delete_an_order()
    {
        $order = Order::make()
            ->id('123')
            ->site('default')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']);

        $order->save();

        $this->assertFileExists($order->path());

        $this->repo->delete($order);

        $this->assertFileDoesNotExist($order->path());
    }
}
