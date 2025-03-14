<?php

namespace DuncanMcClean\Cargo\Contracts\Orders;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use Statamic\Fields\Blueprint;

interface OrderRepository
{
    public function all();

    public function query();

    public function find($id): ?Order;

    public function findOrFail($id): Order;

    public function make(): Order;

    public function makeFromCart(Cart $cart): Order;

    public function save(Order $order): void;

    public function delete(Order $order): void;

    public function blueprint(): Blueprint;

    public static function bindings(): array;
}
