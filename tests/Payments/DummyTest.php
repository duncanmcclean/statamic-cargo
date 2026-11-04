<?php

namespace Tests\Payments;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Exceptions\PreventCheckout;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Payments\Gateways\Dummy;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('payments')]
class DummyTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_is_available_outside_of_production()
    {
        $this->assertTrue((new Dummy)->isAvailable(Cart::make()));
    }

    #[Test]
    public function it_is_unavailable_in_production()
    {
        $this->runInProduction();

        $this->assertFalse((new Dummy)->isAvailable(Cart::make()));
    }

    #[Test]
    public function it_can_process_a_payment()
    {
        $order = $this->makeOrder();

        (new Dummy)->process($order);

        $this->assertEquals('payment_received', $order->fresh()->status()->value);
    }

    #[Test]
    public function it_cant_process_a_payment_in_production()
    {
        $this->runInProduction();

        $order = $this->makeOrder();

        $this->expectException(PreventCheckout::class);

        (new Dummy)->process($order);
    }

    #[Test]
    public function it_can_refund_an_order()
    {
        $order = $this->makeOrder();

        (new Dummy)->refund($order, 500);

        $this->assertEquals(500, $order->fresh()->get('amount_refunded'));
    }

    private function runInProduction(): void
    {
        app()->detectEnvironment(fn () => 'production');

        $this->beforeApplicationDestroyed(fn () => app()->detectEnvironment(fn () => 'testing'));
    }

    private function makeOrder(): OrderContract
    {
        $order = Order::make()
            ->status(OrderStatus::PaymentPending)
            ->grandTotal(1000)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 1, 'total' => 1000],
            ]);

        $order->save();

        return $order;
    }
}
