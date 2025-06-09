@use(DuncanMcClean\Cargo\Cargo)
@extends('statamic::layout')
@section('title', __('Tax Classes'))

@section('content')
    @unless ($taxClasses->isEmpty())
        <ui-header title="{{ __('Tax Classes') }}" icon="{{ Cargo::svg('tax-classes') }}">
            <ui-button
                href="{{ cp_route('cargo.tax-classes.create') }}"
                text="{{ __('Create Tax Class') }}"
                variant="primary"
            ></ui-button>
        </ui-header>

        <tax-class-listing
            :initial-rows="{{ json_encode($taxClasses) }}"
            :initial-columns="{{ json_encode($columns) }}"
        ></tax-class-listing>
    @else
        @include(
            'statamic::partials.empty-state',
            [
                'title' => __('Tax Classes'),
                'description' => __('cargo::messages.tax_class_intro'),
                'svg' => 'empty/fieldsets',
                'button_text' => __('Create Tax Class'),
                'button_url' => cp_route('cargo.tax-classes.create'),
            ]
        )
    @endunless

    <x-statamic::docs-callout
        :topic="__('Tax Classes')"
        url="https://builtwithcargo.dev/docs/taxes#tax-classes"
    />
@endsection
