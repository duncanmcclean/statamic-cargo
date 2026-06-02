<?php

namespace Tests\Customers;

use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ForgetCartOnLogoutTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function current_cart_is_forgotten_on_logout()
    {
        $user = User::make()->save();
        $cart = tap(Cart::make()->customer($user))->save();

        Cart::setCurrent($cart);

        $this->assertTrue(Cart::hasCurrentCart());

        Auth::login($user);
        Auth::logout();

        $this->assertFalse(Cart::hasCurrentCart());
    }
}
