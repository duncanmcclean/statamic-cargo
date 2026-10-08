<?php

namespace DuncanMcClean\Cargo\Orders;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Contracts\Orders\QueryBuilder;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Support\Money;
use Illuminate\Support\Collection;
use League\Csv\EscapeFormula;
use League\Csv\Writer;
use Statamic\Fields\Field;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    private array $columns;

    public function __construct(private QueryBuilder $query, ?array $columns = null)
    {
        $this->columns = static::columns()
            ->when($columns, fn ($available) => $available->only($columns))
            ->all();
    }

    public static function columns(): Collection
    {
        $columns = collect([
            'order_number' => __('Order Number'),
            'date' => __('Date'),
            'status' => __('Status'),
            'customer_name' => __('Customer Name'),
            'customer_email' => __('Customer Email'),
            'line_items' => __('Line Items'),
            'sub_total' => __('Subtotal'),
            'discount_total' => __('Discount Total'),
            'shipping_total' => __('Shipping Total'),
            'tax_total' => __('Tax Total'),
            'grand_total' => __('Grand Total'),
            'payment_gateway' => __('Payment Gateway'),
            'shipping_method' => __('Shipping Method'),
            'shipping_option' => __('Shipping Option'),
            'tracking_number' => __('Tracking Number'),
            'shipping_address' => __('Shipping Address'),
            'billing_address' => __('Billing Address'),
        ]);

        $nonExportableFields = ['customer', 'receipt', 'timeline', 'shipping_details', 'payment_details'];

        return $columns->merge(
            Order::blueprint()->fields()->all()
                ->reject(fn (Field $field) => $columns->has($field->handle()))
                ->reject(fn (Field $field) => in_array($field->handle(), $nonExportableFields))
                ->map(fn (Field $field) => __($field->display()))
        );
    }

    public function download(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $writer = Writer::createFromPath('php://output', 'w');
            $writer->addFormatter(new EscapeFormula("'"));

            $writer->insertOne(array_values($this->columns));

            $this->query->lazy(100)->each(fn (OrderContract $order) => $writer->insertOne(
                array_map(fn (string $column) => $this->value($order, $column), array_keys($this->columns))
            ));
        }, 'orders-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function value(OrderContract $order, string $column): mixed
    {
        return match ($column) {
            'order_number' => $order->orderNumber(),
            'date' => $order->date()->toDateTimeString(),
            'status' => OrderStatus::label($order->status()),
            'customer_name' => $order->customer()?->name(),
            'customer_email' => $order->customer()?->email(),
            'line_items' => $this->summariseLineItems($order),
            'sub_total' => Money::format($order->subTotal(), $order->site()),
            'discount_total' => Money::format($order->discountTotal(), $order->site()),
            'shipping_total' => Money::format($order->shippingTotal(), $order->site()),
            'tax_total' => Money::format($order->taxTotal(), $order->site()),
            'grand_total' => Money::format($order->grandTotal(), $order->site()),
            'payment_gateway' => $order->paymentGateway()?->title(),
            'shipping_method' => $order->shippingMethod()?->title(),
            'shipping_option' => $order->shippingOption()?->name(),
            'shipping_address' => (string) $order->shippingAddress(),
            'billing_address' => (string) $order->billingAddress(),
            default => $this->fieldValue($order, $column),
        };
    }

    private function summariseLineItems(OrderContract $order): string
    {
        return $order->lineItems()
            ->map(function (LineItem $lineItem) {
                $title = $lineItem->product()?->get('title');

                if ($variant = $lineItem->variant()) {
                    $title .= " ({$variant->name()})";
                }

                return "{$lineItem->quantity()} x {$title}";
            })
            ->implode('; ');
    }

    private function fieldValue(OrderContract $order, string $column): mixed
    {
        $value = $order->get($column);

        if ($order->blueprint()->field($column)?->type() === 'money') {
            return Money::format($value ?? 0, $order->site());
        }

        return is_array($value) ? collect($value)->flatten()->implode(', ') : $value;
    }
}
