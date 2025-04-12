<?php

namespace Tests\Discounts;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Events\DiscountCreated;
use DuncanMcClean\Cargo\Events\DiscountDeleted;
use DuncanMcClean\Cargo\Events\DiscountSaved;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function is_valid_when_line_item_product_is_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $discount = Discount::make()
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10)
            ->set('products', ['product-1']);

        $this->assertTrue($discount->discountType()->isValidForLineItem($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_line_item_product_is_not_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $discount = Discount::make()
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10)
            ->set('products', ['product-2']);

        $this->assertFalse($discount->discountType()->isValidForLineItem($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_valid_when_customer_is_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $discount = Discount::make()
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10)
            ->set('customers', [$user->id()]);

        $this->assertTrue($discount->discountType()->isValidForLineItem($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_customer_is_not_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $discount = Discount::make()
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10)
            ->set('customers', ['abc']);

        $this->assertFalse($discount->discountType()->isValidForLineItem($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_customer_is_a_guest()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()
            ->customer([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $discount = Discount::make()
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10)
            ->set('customers', ['abc']);

        $this->assertFalse($discount->discountType()->isValidForLineItem($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_before_start_date()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $discount = Discount::make()
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10)
            ->set('start_date', '2030-01-01');

        $this->assertFalse($discount->discountType()->isValidForLineItem($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_valid_between_dates()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $discount = Discount::make()
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10)
            ->set('start_date', '2024-01-01')
            ->set('end_date', '2030-01-01');

        $this->assertTrue($discount->discountType()->isValidForLineItem($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_after_end_date()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $discount = Discount::make()
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10)
            ->set('end_date', '2024-01-01');

        $this->assertFalse($discount->discountType()->isValidForLineItem($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function discount_can_be_saved()
    {
        Event::fake();

        $this->assertNull(Discount::find('abc'));

        $discount = Discount::make()
            ->id('abc')
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10);

        $discount->save();

        $this->assertInstanceOf(DiscountContract::class, $discount = Discount::find($discount->id()));
        $this->assertEquals('abc', $discount->id());
        $this->assertFileExists($discount->path());
        $this->assertStringContainsString('content/cargo/discounts/foo-bar.yaml', $discount->path());

        $this->assertEquals(<<<'YAML'
id: abc
name: 'Foo Bar'
type: percentage_off
percentage_off: 10

YAML
            , file_get_contents($discount->path()));

        Event::assertDispatched(DiscountCreated::class, function ($event) use ($discount) {
            return $event->discount->id() === $discount->id();
        });

        Event::assertDispatched(DiscountSaved::class, function ($event) use ($discount) {
            return $event->discount->id() === $discount->id();
        });
    }

    #[Test]
    public function discount_can_be_saved_quietly()
    {
        Event::fake();

        $this->assertNull(Discount::find('abc'));

        $discount = Discount::make()
            ->id('abc')
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10);

        $discount->saveQuietly();

        $this->assertInstanceOf(DiscountContract::class, $discount = Discount::find($discount->id()));
        $this->assertEquals('abc', $discount->id());
        $this->assertFileExists($discount->path());
        $this->assertStringContainsString('content/cargo/discounts/foo-bar.yaml', $discount->path());

        $this->assertEquals(<<<'YAML'
id: abc
name: 'Foo Bar'
type: percentage_off
percentage_off: 10

YAML
            , file_get_contents($discount->path()));

        Event::assertNotDispatched(DiscountCreated::class, function ($event) use ($discount) {
            return $event->discount->id() === $discount->id();
        });

        Event::assertNotDispatched(DiscountSaved::class, function ($event) use ($discount) {
            return $event->discount->id() === $discount->id();
        });
    }

    #[Test]
    public function discount_can_be_deleted()
    {
        Event::fake();

        $discount = Discount::make()
            ->id('abc')
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10);

        $discount->save();

        $this->assertNotNull(Discount::find('abc'));

        $discount->delete();

        $this->assertNull(Discount::find('abc'));

        Event::assertDispatched(DiscountDeleted::class, function ($event) use ($discount) {
            return $event->discount->id() === $discount->id();
        });
    }

    #[Test]
    public function discount_can_be_deleted_quietly()
    {
        Event::fake();

        $discount = Discount::make()
            ->id('abc')
            ->name('Foo Bar')
            ->type('percentage_off')
            ->set('percentage_off', 10);

        $discount->save();

        $this->assertNotNull(Discount::find('abc'));

        $discount->deleteQuietly();

        $this->assertNull(Discount::find('abc'));

        Event::assertNotDispatched(DiscountDeleted::class, function ($event) use ($discount) {
            return $event->discount->id() === $discount->id();
        });
    }
}
