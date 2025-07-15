@php
    use DuncanMcClean\Cargo\Cargo;
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Tax Classes'))

@section('content')
    <ui-header title="{{ __('Tax Classes') }}" icon="{{ Cargo::svg('tax-classes') }}">
        <ui-button
            href="{{ cp_route('cargo.tax-classes.create') }}"
            text="{{ __('Create Tax Class') }}"
            variant="primary"
        ></ui-button>
    </ui-header>

    <tax-class-listing
        :initial-items="{{ json_encode($taxClasses) }}"
        :initial-columns="{{ json_encode($columns) }}"
    ></tax-class-listing>

    <x-statamic::docs-callout
        :topic="__('Tax Classes')"
        url="https://builtwithcargo.dev/docs/taxes#tax-classes"
    />
@endsection
