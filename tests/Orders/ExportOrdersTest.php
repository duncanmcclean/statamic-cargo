<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Contracts\Orders\OrderRepository as OrderRepositoryContract;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\Eloquent\LineItemModel;
use DuncanMcClean\Cargo\Orders\Eloquent\OrderModel;
use DuncanMcClean\Cargo\Orders\Eloquent\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ExportOrdersTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
        Entry::make()->id('t-shirt')->collection('products')->data(['title' => 'T-shirt'])->save();
        Entry::make()->id('hoodie')->collection('products')->data(['title' => 'Hoodie'])->save();
    }

    #[Test]
    public function can_export_orders()
    {
        $this->makeOrders();

        $csv = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.orders.export', ['columns' => 'order_number,date,status,customer_name,customer_email,line_items,grand_total']))
            ->assertOk()
            ->assertDownload()
            ->streamedContent();

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'CSV'
"Order Number",Date,Status,"Customer Name","Customer Email","Line Items","Grand Total"
1002,"2026-10-02 14:30:00",Shipped,"Jane Doe",jane@example.com,"1 x Hoodie",£40.00
1001,"2026-10-01 09:00:00","Payment Received","John Smith",john@example.com,"2 x T-shirt; 1 x Hoodie",£70.00

CSV, $csv);
    }

    #[Test]
    public function can_export_orders_from_eloquent_repository()
    {
        $this->useEloquentOrders();
        $this->makeOrders();

        $this->assertDatabaseCount('cargo_orders', 2);

        $csv = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.orders.export', ['columns' => 'order_number,customer_email,line_items']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringEqualsStringIgnoringLineEndings(<<<'CSV'
"Order Number","Customer Email","Line Items"
1002,jane@example.com,"1 x Hoodie"
1001,john@example.com,"2 x T-shirt; 1 x Hoodie"

CSV, $csv);
    }

    #[Test]
    public function exports_every_column_when_none_are_selected()
    {
        $this->makeOrders();

        $csv = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.orders.export'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringStartsWith(
            '"Order Number",Date,Status,"Customer Name","Customer Email","Line Items",Subtotal,"Discount Total","Shipping Total","Tax Total","Grand Total"',
            $csv
        );
    }

    #[Test]
    #[DataProvider('listingParametersProvider')]
    public function can_export_orders_using_listing_parameters(array $parameters, string $expectedOrderNumbers)
    {
        $this->makeOrders();

        $csv = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('cargo.orders.export', [...$parameters, 'columns' => 'order_number']))
            ->assertOk()
            ->streamedContent();

        $this->assertEquals("\"Order Number\"\n{$expectedOrderNumbers}\n", $csv);
    }

    public static function listingParametersProvider(): array
    {
        return [
            'search' => [['search' => '1001'], '1001'],
            'filters' => [['filters' => base64_encode(json_encode(['order_status' => ['status' => 'shipped']]))], '1002'],
            'sort' => [['sort' => 'order_number', 'order' => 'asc'], "1001\n1002"],
        ];
    }

    #[Test]
    public function cant_export_orders_without_permissions()
    {
        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('cargo.orders.export'))
            ->assertRedirect('/cp');
    }

    private function makeOrders(): void
    {
        Order::make()
            ->orderNumber(1001)
            ->date(Carbon::parse('2026-10-01 09:00:00'))
            ->status('payment_received')
            ->customer(['name' => 'John Smith', 'email' => 'john@example.com'])
            ->lineItems([
                ['id' => 'one', 'product' => 't-shirt', 'quantity' => 2, 'total' => 3000],
                ['id' => 'two', 'product' => 'hoodie', 'quantity' => 1, 'total' => 4000],
            ])
            ->grandTotal(7000)
            ->save();

        Order::make()
            ->orderNumber(1002)
            ->date(Carbon::parse('2026-10-02 14:30:00'))
            ->status('shipped')
            ->customer(['name' => 'Jane Doe', 'email' => 'jane@example.com'])
            ->lineItems([
                ['id' => 'three', 'product' => 'hoodie', 'quantity' => 1, 'total' => 4000],
            ])
            ->grandTotal(4000)
            ->save();
    }

    private function useEloquentOrders(): void
    {
        config()->set('statamic.cargo.orders', [
            'repository' => 'eloquent',
            'model' => OrderModel::class,
            'table' => 'cargo_orders',
        ]);

        $this->app->bind('cargo.orders.eloquent.model', fn () => OrderModel::class);
        $this->app->bind('cargo.orders.eloquent.line_items_model', fn () => LineItemModel::class);

        Statamic::repository(OrderRepositoryContract::class, OrderRepository::class);
    }
}
