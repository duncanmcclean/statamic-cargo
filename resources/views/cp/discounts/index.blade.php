@extends('statamic::layout')
@section('title', __('Discounts'))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="flex-1">{{ __('Discounts') }}</h1>

        @if (auth()->user()->can('create discounts'))
            <a class="btn-primary" href="{{ cp_route('cargo.discounts.create') }}">{{ __('Create Discount') }}</a>
        @endif
    </div>

    <discounts-listing
        sort-column="code"
        sort-direction="asc"
        :initial-columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        :action-url="{{ json_encode(cp_route('cargo.discounts.actions.run')) }}"
    ></discounts-listing>

    <x-statamic::docs-callout :topic="__('Discounts')" url="https://builtwithcargo.dev/docs/discounts" />
@endsection
