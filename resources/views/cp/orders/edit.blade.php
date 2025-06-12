@use(DuncanMcClean\Cargo\Cargo)
@use(Statamic\CP\Breadcrumbs\Breadcrumbs)

@extends('statamic::layout')
@section('title', Breadcrumbs::title($title))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <order-publish-form
        :blueprint="{{ json_encode($blueprint) }}"
        icon="{{ Cargo::svg('orders') }}"
        initial-title="{{ $title }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-read-only="{{ json_encode($readOnly) }}"
        :actions="{{ json_encode($actions) }}"
        :item-actions="{{ json_encode($itemActions) }}"
        item-action-url="{{ cp_route('cargo.orders.actions.run') }}"
        packing-slip-url="{{ cp_route('cargo.orders.packing-slip', $order->id()) }}"
        :can-edit-blueprint="{{ json_encode($canEditBlueprint) }}"
    ></order-publish-form>
@endsection
