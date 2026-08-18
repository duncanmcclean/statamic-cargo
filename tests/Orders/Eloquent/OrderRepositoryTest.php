<?php

namespace Tests\Orders\Eloquent;

use DuncanMcClean\Cargo\Events\OrderStatusUpdated;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\Eloquent\OrderModel;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk, RefreshDatabase;

    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.cargo.orders', [
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class,
            'table' => 'cargo_orders',
        ]);

        $this->app->bind('cargo.orders.eloquent.model', function () {
            return config('statamic.cargo.orders.model', \DuncanMcClean\Cargo\Orders\Eloquent\OrderModel::class);
        });

        $this->app->bind('cargo.orders.eloquent.line_items_model', function () {
            return config('statamic.cargo.orders.line_items_model', \DuncanMcClean\Cargo\Orders\Eloquent\LineItemModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\Cargo\Contracts\Orders\OrderRepository::class,
            \DuncanMcClean\Cargo\Orders\Eloquent\OrderRepository::class
        );

        $this->repo = $this->app->make(\DuncanMcClean\Cargo\Contracts\Orders\OrderRepository::class);
    }

    #[Test]
    public function can_find_orders()
    {
        Cart::make()->id('abc')->save();

        $model = OrderModel::create([
            'order_number' => 1234,
            'date' => now(),
            'site' => 'default',
            'cart' => 'abc',
            'status' => 'payment_pending',
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

        $order = $this->repo->find($model->id);

        $this->assertEquals($model->id, $order->id());
        $this->assertEquals($model->order_number, $order->orderNumber());
        $this->assertEquals($model->date, $order->date());
        $this->assertEquals($model->site, $order->site()->handle());
        $this->assertEquals($model->cart, $order->cart());
        $this->assertEquals($model->status, $order->status());
        $this->assertEquals(json_decode($model->customer, true)['name'], $order->customer()->name());
        $this->assertEquals(json_decode($model->customer, true)['email'], $order->customer()->email());
        $this->assertEquals($model->grand_total, $order->grandTotal());
        $this->assertEquals($model->sub_total, $order->subTotal());
        $this->assertEquals($model->discount_total, $order->discountTotal());
        $this->assertEquals($model->tax_total, $order->taxTotal());
        $this->assertEquals($model->shipping_total, $order->shippingTotal());
        $this->assertEquals($model->data, $order->data()->except('updated_at')->all());

        $this->assertEquals('123', $order->lineItems()->first()->id());
        $this->assertEquals(2500, $order->lineItems()->first()->total());
    }

    #[Test]
    public function can_save_an_order()
    {
        $order = Order::make()
            ->site('default')
            ->cart('abc')
            ->status('payment_pending')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0)
            ->lineItems([['id' => '123', 'product' => 'abc', 'quantity' => 1, 'total' => 2500]])
            ->data($data = ['foo' => 'bar']);

        $this->repo->save($order);

        $this->assertDatabaseHas('cargo_orders', [
            'site' => 'default',
            'grand_total' => 2500,
            'data->foo' => 'bar',
        ]);

        $this->assertDatabaseHas('cargo_order_line_items', [
            'order_id' => $order->id(),
            'product' => 'abc',
            'quantity' => 1,
            'total' => 2500,
        ]);

        $this->assertNotNull($order->id());
        $this->assertEquals(1, $order->orderNumber());
        $this->assertNotNull($order->date());
    }

    #[Test]
    public function date_does_not_shift_when_app_timezone_is_not_utc()
    {
        config()->set('app.timezone', 'Europe/Berlin');
        date_default_timezone_set('Europe/Berlin');

        $date = Carbon::parse('2025-08-18 10:00:00', 'UTC');

        $order = Order::make()
            ->site('default')
            ->cart('abc')
            ->status('payment_pending')
            ->date($date)
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0);

        $this->repo->save($order);

        $this->assertTrue($order->date()->equalTo($date));

        $order = $this->repo->find($order->id());

        $this->assertTrue($order->date()->equalTo($date));

        $this->repo->save($order);

        $this->assertTrue($order->date()->equalTo($date));
    }

    #[Test]
    public function order_status_updated_event_is_dispatched_when_status_changes_on_an_order()
    {
        Cart::make()->id('abc')->save();

        $model = OrderModel::create([
            'order_number' => 1234,
            'date' => now(),
            'site' => 'default',
            'cart' => 'abc',
            'status' => 'payment_pending',
            'customer' => json_encode(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']),
            'grand_total' => 2500,
            'sub_total' => 2500,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'data' => ['foo' => 'bar'],
        ]);

        $order = $this->repo->find($model->id);

        Event::fake();

        $order->status(OrderStatus::PaymentReceived)->save();

        Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($order) {
            return $event->order->id() === $order->id()
                && $event->originalStatus === OrderStatus::PaymentPending
                && $event->updatedStatus === OrderStatus::PaymentReceived;
        });
    }

    #[Test]
    public function can_delete_an_order()
    {
        $order = Order::make()
            ->id('123')
            ->site('default')
            ->cart('abc')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']);

        $order->save();

        $this->repo->delete($order);

        $this->assertDatabaseMissing('cargo_orders', [
            'id' => '123',
            'site' => 'default',
            'cart' => 'abc',
        ]);
    }
}
