@use(DuncanMcClean\Cargo\Cargo)
@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Discounts'))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <ui-header title="{{ __('Discounts') }}" icon="{{ Cargo::svg('discounts') }}">
        @if (auth()->user()->can('create discounts'))
            <ui-button
                href="{{ cp_route('cargo.discounts.create') }}"
                text="{{ __('Create Discount') }}"
                variant="primary"
            ></ui-button>
        @endif
    </ui-header>

    <discounts-listing
        sort-column="code"
        sort-direction="asc"
        :initial-columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        :action-url="{{ json_encode(cp_route('cargo.discounts.actions.run')) }}"
    ></discounts-listing>

    <x-statamic::docs-callout :topic="__('Discounts')" url="https://builtwithcargo.dev/docs/discounts" />
@endsection
