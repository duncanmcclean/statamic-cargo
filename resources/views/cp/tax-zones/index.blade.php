@use(DuncanMcClean\Cargo\Cargo)
@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Tax Zones'))

@section('content')
    @unless ($taxZones->isEmpty())
        <ui-header title="{{ __('Tax Zones') }}" icon="{{ Cargo::svg('tax-zones') }}">
            <ui-button
                href="{{ cp_route('cargo.tax-zones.create') }}"
                text="{{ __('Create Tax Zone') }}"
                variant="primary"
            ></ui-button>
        </ui-header>

        <tax-class-listing
            :initial-rows="{{ json_encode($taxZones) }}"
            :initial-columns="{{ json_encode($columns) }}"
        ></tax-class-listing>
    @else
        <x-statamic::empty-screen
            title="{{ __('Tax Zones') }}"
            description="{{ __('cargo::messages.tax_zones_intro') }}"
            svg="empty/content"
            button_text="{{ __('Create Tax Zone') }}"
            button_url="{{ cp_route('cargo.tax-zones.create') }}"
        />
    @endunless

    <x-statamic::docs-callout :topic="__('Tax Zones')" url="https://builtwithcargo.dev/docs/taxes#tax-zones" />
@endsection
