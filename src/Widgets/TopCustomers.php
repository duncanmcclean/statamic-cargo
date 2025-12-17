<?php

namespace DuncanMcClean\Cargo\Widgets;

use DuncanMcClean\Cargo\Facades\Order;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Users\ListedUser;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class TopCustomers extends Widget
{
    public function component()
    {
        $blueprint = User::blueprint();

        $columns = $blueprint
            ->columns()
            ->only($this->config('fields', []))
            ->map(fn ($column) => $column->sortable(false)->visible(true))
            ->values();

        $customers = Order::query()
            ->where('customer', 'not like', 'guest::%') // TODO: extract into whereNotGuestCustomer() method
            ->getByCustomer()
            ->sortByDesc(fn ($orders): int => $orders->count())
            ->take($this->config('limit', 5))
            ->map(function ($orders, $id) use ($blueprint, $columns) {
                $user = User::find($id);

                if (! $user) {
                    return null;
                }

                // todo: try and get ListedUser working here instead.

                return $columns
                    ->map->field
                    ->mapWithKeys(function ($key) use ($blueprint, $user) {
                        $field = $blueprint->field($key);

                        $value = $user->value($key) ?? $field->defaultValue();

                        if ($field) {
                            $value = $field->setValue($value)->preProcessIndex()->value();
                        }

                        return [$key => $value];
                    })
                    ->merge([
                        'id' => $user->id(),
                        'email' => $user->email(),
                        'orders_count' => $orders->count(),
                        'edit_url' => $user->editUrl(),
                        'avatar' => $user->avatar(),
                        'initials' => $user->initials(),
                    ])
                    ->all();
            })
            ->filter()
            ->values();

        return VueComponent::render('top-customers-widget', [
            'title' => $this->config('title', __('Top Customers')),
            'additionalColumns' => $columns,
            'listingUrl' => cp_route('users.index'),
            'topCustomers' => $customers,
        ]);
    }
}
