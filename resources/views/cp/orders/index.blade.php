@extends('statamic::layout')
@section('title', __('Orders'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="flex-1">{{ __('Orders') }}</h1>

        @can('configure fields')
            <dropdown-list class="ltr:mr-2 rtl:ml-2">
                <dropdown-item
                    :text="__('Edit Blueprint')"
                    redirect="{{ cp_route('blueprints.edit', ['cargo', 'order']) }}"
                ></dropdown-item>
            </dropdown-list>
        @endcan
    </div>

    <orders-listing
        sort-column="order_number"
        sort-direction="desc"
        :initial-columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        :action-url="{{ json_encode(cp_route('cargo.orders.actions.run')) }}"
    ></orders-listing>

    <x-statamic::docs-callout :topic="__('Orders')" url="https://builtwithcargo.dev/docs/carts-and-orders" />
@endsection
