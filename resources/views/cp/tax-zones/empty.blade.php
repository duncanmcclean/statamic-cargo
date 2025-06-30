@php
    use DuncanMcClean\Cargo\Cargo;
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Tax Zones'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2">
            <ui-icon name="{{ Cargo::svg('tax-zones') }}" class="size-5 text-gray-500"></ui-icon>
            {{ __('Tax Zones') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('cargo::messages.tax_zones_intro') }}">
        <ui-empty-state-item
            href="{{ cp_route('cargo.tax-zones.create') }}"
            icon="{{ Cargo::svg('tax-zones') }}"
            heading="{{ __('Create a Tax Zone') }}"
            description="{{ __('Get started by creating your first tax zone.') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        :topic="__('Tax Zones')"
        url="https://builtwithcargo.dev/docs/taxes#tax-zones"
    />
@stop
