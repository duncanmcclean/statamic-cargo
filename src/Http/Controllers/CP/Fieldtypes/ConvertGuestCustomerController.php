<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Fieldtypes;

use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Fieldtypes\CustomerFieldtype;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class ConvertGuestCustomerController extends CpController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'order_id' => ['required', 'string'],
        ]);

        $order = Order::find($validated['order_id']);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::make()
                ->email($validated['email'])
                ->data(Arr::except($order->customer()->toArray(), ['email']));

            $user->save();
        }

        Order::query()
            ->where('customer', "guest::{$validated['email']}")
            ->get()
            ->each(fn ($order) => $order->customer($user->id())->save());

        return (new CustomerFieldtype)->preProcess($user);
    }
}
