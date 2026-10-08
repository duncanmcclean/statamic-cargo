<?php

namespace DuncanMcClean\Cargo\Http\Controllers\CP\Orders;

use Illuminate\Support\Str;
use Statamic\Facades\User;

trait QueriesOrderSearch
{
    protected function applyOrderSearch($query, ?string $search)
    {
        if (! $search) {
            return;
        }

        $query
            ->where('id', $search)
            ->orWhere('date', 'LIKE', '%'.$search.'%')
            ->orWhere('order_number', 'LIKE', '%'.Str::remove('#', $search).'%')
            ->orWhere(function ($query) use ($search) {
                $users = User::query()
                    ->where('email', 'LIKE', '%'.$search.'%')
                    ->when(User::blueprint()->hasField('first_name'), function ($query) use ($search) {
                        foreach (explode(' ', $search) as $word) {
                            $query
                                ->orWhere('first_name', 'LIKE', '%'.$word.'%')
                                ->orWhere('last_name', 'LIKE', '%'.$word.'%');
                        }
                    }, function ($query) use ($search) {
                        $query->orWhere('name', 'LIKE', '%'.$search.'%');
                    })
                    ->pluck('id')
                    ->all();

                $query->whereIn('customer', $users);
            })
            ->orWhere('customer', "guest::$search%");
    }
}
