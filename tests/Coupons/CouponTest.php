<?php

namespace Tests\Coupons;

use DuncanMcClean\Cargo\Contracts\Coupons\Coupon as CouponContract;
use DuncanMcClean\Cargo\Coupons\CouponType;
use DuncanMcClean\Cargo\Events\CouponCreated;
use DuncanMcClean\Cargo\Events\CouponDeleted;
use DuncanMcClean\Cargo\Events\CouponSaved;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Coupon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function is_valid_when_line_item_product_is_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('products', ['product-1']);

        $this->assertTrue($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_line_item_product_is_not_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('products', ['product-2']);

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_valid_when_customer_is_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'specific_customers')
            ->set('customers', [$user->id()]);

        $this->assertTrue($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_customer_is_not_in_allowlist()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'specific_customers')
            ->set('customers', ['abc']);

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
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

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'specific_customers')
            ->set('customers', ['abc']);

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_valid_when_customer_email_matches_domain()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'customers_by_domain')
            ->set('customers_by_domain', ['example.com']);

        $this->assertTrue($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_when_customer_email_does_not_match_domain()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $user = tap(User::make()->email('cj.cregg@example.com'))->save();

        $cart = Cart::make()->customer($user)->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('customer_eligibility', 'customers_by_domain')
            ->set('customers_by_domain', ['statamic.com']);

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_before_valid_from_timestamp()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('valid_from', '2030-01-01');

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_valid_between_timestamps()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('valid_from', '2024-01-01')
            ->set('expires_at', '2030-01-01');

        $this->assertTrue($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function is_not_valid_after_coupon_has_expired()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()->lineItems([['product' => 'product-1', 'quantity' => 1]]);

        $coupon = Coupon::make()
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10)
            ->set('expires_at', '2024-01-01');

        $this->assertFalse($coupon->isValid($cart, $cart->lineItems()->first()));
    }

    #[Test]
    public function coupon_can_be_saved()
    {
        Event::fake();

        $this->assertNull(Coupon::find('abc'));

        $coupon = Coupon::make()
            ->id('abc')
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10);

        $coupon->save();

        $this->assertInstanceOf(CouponContract::class, $coupon = Coupon::find($coupon->id()));
        $this->assertEquals('abc', $coupon->id());
        $this->assertFileExists($coupon->path());
        $this->assertStringContainsString('content/cargo/coupons/FOOBAR.yaml', $coupon->path());

        $this->assertEquals(<<<'YAML'
id: abc
amount: 10
type: percentage

YAML
            , file_get_contents($coupon->path()));

        Event::assertDispatched(CouponCreated::class, function ($event) use ($coupon) {
            return $event->coupon->id() === $coupon->id();
        });

        Event::assertDispatched(CouponSaved::class, function ($event) use ($coupon) {
            return $event->coupon->id() === $coupon->id();
        });
    }

    #[Test]
    public function coupon_can_be_saved_quietly()
    {
        Event::fake();

        $this->assertNull(Coupon::find('abc'));

        $coupon = Coupon::make()
            ->id('abc')
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10);

        $coupon->saveQuietly();

        $this->assertInstanceOf(CouponContract::class, $coupon = Coupon::find($coupon->id()));
        $this->assertEquals('abc', $coupon->id());
        $this->assertFileExists($coupon->path());
        $this->assertStringContainsString('content/cargo/coupons/FOOBAR.yaml', $coupon->path());

        $this->assertEquals(<<<'YAML'
id: abc
amount: 10
type: percentage

YAML
            , file_get_contents($coupon->path()));

        Event::assertNotDispatched(CouponCreated::class, function ($event) use ($coupon) {
            return $event->coupon->id() === $coupon->id();
        });

        Event::assertNotDispatched(CouponSaved::class, function ($event) use ($coupon) {
            return $event->coupon->id() === $coupon->id();
        });
    }

    #[Test]
    public function coupon_can_be_deleted()
    {
        Event::fake();

        $coupon = Coupon::make()
            ->id('abc')
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10);

        $coupon->save();

        $this->assertNotNull(Coupon::find('abc'));

        $coupon->delete();

        $this->assertNull(Coupon::find('abc'));

        Event::assertDispatched(CouponDeleted::class, function ($event) use ($coupon) {
            return $event->coupon->id() === $coupon->id();
        });
    }

    #[Test]
    public function coupon_can_be_deleted_quietly()
    {
        Event::fake();

        $coupon = Coupon::make()
            ->id('abc')
            ->code('FOOBAR')
            ->type(CouponType::Percentage)
            ->amount(10);

        $coupon->save();

        $this->assertNotNull(Coupon::find('abc'));

        $coupon->deleteQuietly();

        $this->assertNull(Coupon::find('abc'));

        Event::assertNotDispatched(CouponDeleted::class, function ($event) use ($coupon) {
            return $event->coupon->id() === $coupon->id();
        });
    }
}
