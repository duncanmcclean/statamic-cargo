@use(DuncanMcClean\Cargo\Cargo)
@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Orders'))
@section('wrapper_class', 'max-w-full')

@section('content')
    <ui-header title="{{ __('Orders') }}" icon="{{ Cargo::svg('orders') }}">
        @can('configure fields')
            <ui-dropdown placement="left-start" class="me-2">
                <ui-dropdown-menu>
                    <ui-dropdown-item
                        :text="__('Edit Blueprint')"
                        icon="blueprint-edit"
                        redirect="{{ cp_route('blueprints.edit', ['cargo', 'order']) }}"
                    ></ui-dropdown-item>
                </ui-dropdown-menu>
            </ui-dropdown>
        @endcan
    </ui-header>

    <orders-listing
        sort-column="order_number"
        sort-direction="desc"
        :initial-columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        :action-url="{{ json_encode(cp_route('cargo.orders.actions.run')) }}"
    ></orders-listing>

    <x-statamic::docs-callout :topic="__('Orders')" url="https://builtwithcargo.dev/docs/carts-and-orders" />
@endsection
