@use(Statamic\CP\Breadcrumbs\Breadcrumbs)
@extends('statamic::layout')
@section('title', Breadcrumbs::title($title))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <order-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        initial-title="{{ $title }}"
        initial-reference="{{ $order->reference() }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-read-only="{{ json_encode($readOnly) }}"
        initial-listing-url="{{ cp_route('cargo.orders.index') }}"
        :initial-item-actions="{{ json_encode($itemActions) }}"
        item-action-url="{{ cp_route('cargo.orders.actions.run') }}"
    ></order-publish-form>
@endsection
