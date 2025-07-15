@php
    use DuncanMcClean\Cargo\Cargo;
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Tax Zones'))

@section('content')
    <ui-header title="{{ __('Tax Zones') }}" icon="{{ Cargo::svg('tax-zones') }}">
        <ui-button
            href="{{ cp_route('cargo.tax-zones.create') }}"
            text="{{ __('Create Tax Zone') }}"
            variant="primary"
        ></ui-button>
    </ui-header>

    <tax-zone-listing
        :initial-items="{{ json_encode($taxZones) }}"
        :initial-columns="{{ json_encode($columns) }}"
    ></tax-zone-listing>

    <x-statamic::docs-callout
        :topic="__('Tax Zones')"
        url="https://builtwithcargo.dev/docs/taxes#tax-zones"
    />
@endsection
