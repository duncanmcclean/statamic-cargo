<?php

namespace Tests\Stache\Repositories;

use DuncanMcClean\Cargo\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\YAML;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartRepositoryTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();

        $this->repo = $this->app->make(\DuncanMcClean\Cargo\Contracts\Cart\CartRepository::class);
    }

    #[Test]
    public function can_find_carts()
    {
        Cart::make()
            ->id('abc')
            ->site('default')
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

        $cart = $this->repo->find('abc');

        $this->assertEquals('abc', $cart->id());
        $this->assertEquals('default', $cart->site()->handle());
        $this->assertEquals('CJ Cregg', $cart->customer()->name());
        $this->assertEquals('cj.cregg@whitehouse.gov', $cart->customer()->email());
        $this->assertEquals(2500, $cart->grandTotal());
        $this->assertEquals(2500, $cart->subTotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(0, $cart->taxTotal());
        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(['foo' => 'bar'], $cart->data()->except('updated_at', 'fingerprint')->all());

        $this->assertEquals('123', $cart->lineItems()->first()->id());
        $this->assertEquals(2500, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function can_save_a_cart()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('abc')->data(['price' => 2500])->save();

        $cart = Cart::make()
            ->id('abc')
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

        $this->assertStringContainsString('content/cargo/carts/abc.yaml', $cart->path());

        $yaml = YAML::file($cart->path())->parse();

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
    public function can_delete_a_cart()
    {
        $cart = Cart::make()
            ->id('123')
            ->site('default')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']);

        $cart->save();

        $this->assertFileExists($cart->path());

        $this->repo->delete($cart);

        $this->assertFileDoesNotExist($cart->path());
    }
}
