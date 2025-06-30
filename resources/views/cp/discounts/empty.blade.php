@php
    use DuncanMcClean\Cargo\Cargo;
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Discounts'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
<header class="mt-8 py-8 text-center">
    <h1 class="flex items-center justify-center gap-2 text-[25px] font-medium antialiased">
        <ui-icon name="{{ Cargo::svg('discounts') }}" class="size-5 text-gray-500"></ui-icon>
        {{ __('Discounts') }}
    </h1>
</header>

<ui-empty-state-menu heading="{{ __('cargo::messages.discount_configure_intro') }}">
    <ui-empty-state-item
        href="{{ cp_route('cargo.discounts.create') }}"
        icon="{{ Cargo::svg('discounts') }}"
        heading="{{ __('Create a Discount') }}"
        description="{{ __('Get started by creating your first discount.') }}"
    ></ui-empty-state-item>
</ui-empty-state-menu>

<x-statamic::docs-callout :topic="__('Discounts')" url="https://builtwithcargo.dev/docs/discounts" />
@stop
