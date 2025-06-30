<?php

namespace DuncanMcClean\Cargo\Actions;

use DuncanMcClean\Cargo\Cargo;
use DuncanMcClean\Cargo\Contracts;
use DuncanMcClean\Cargo\Events\OrderRefunded;
use Statamic\Actions\Action;

class Refund extends Action
{
    protected $dangerous = true;

    public static function title()
    {
        return __('Refund');
    }

    public function icon(): string
    {
        return Cargo::svg('refund');
    }

    public function visibleTo($item)
    {
        return $item instanceof Contracts\Orders\Order
            && $item->paymentGateway()
            && $item->get('amount_refunded') < $item->grandTotal();
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($user, $item)
    {
        return $user->can('refund', $item) && $item->paymentGateway();
    }

    protected function fieldItems()
    {
        $order = $this->items->first();

        return [
            'amount' => [
                'display' => __('Amount'),
                'instructions' => __('Enter the amount you wish to refund the customer.'),
                'type' => 'money',
                'default' => $order->grandTotal() - $order->get('amount_refunded'),
                'validate' => 'required|numeric',
            ],
        ];
    }

    /**
     * We're overriding this method in order to set the "parent" of the fields,
     * which is used to determine the currency of the Amount field.
     *
     * @return \Statamic\Fields\Fields
     */
    public function fields()
    {
        $fields = parent::fields();

        if ($order = $this->items->first()) {
            $fields->setParent($order);
        }

        return $fields;
    }

    public function buttonText()
    {
        /** @translation */
        return 'Refund';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to refund this order?';
    }

    public function bypassesDirtyWarning(): bool
    {
        return true;
    }

    public function run($items, $values)
    {
        $order = collect($items)->first();
        $amountRemaining = $order->grandTotal() - $order->get('amount_refunded');

        if ($values['amount'] <= 0) {
            throw new \Exception(__('cargo::validation.refund_greater_than_zero'));
        }

        if ($amountRemaining < $values['amount']) {
            throw new \Exception(__('cargo::validation.refund_greater_than_remaining'));
        }

        $order->paymentGateway()->refund($order, $values['amount']);

        OrderRefunded::dispatch($order->fresh(), $values['amount']);
    }
}
