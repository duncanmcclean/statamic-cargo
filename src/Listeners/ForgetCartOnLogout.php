<?php

namespace DuncanMcClean\Cargo\Listeners;

use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Auth\Events\Logout;

class ForgetCartOnLogout
{
    public function handle(Logout $event): void
    {
        Cart::forgetCurrentCart();
    }
}
