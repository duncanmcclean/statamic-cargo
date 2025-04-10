@extends('statamic::layout')
@section('title', __('Discounts'))

@section('content')

@include(
    'statamic::partials.empty-state',
    [
        'title' => __('Discounts'),
        'description' => __('cargo::messages.discount_configure_intro'),
        'svg' => 'empty/fieldsets',
        'button_text' => __('Create Discount'),
        'button_url' => cp_route('cargo.discounts.create'),
        'can' => Auth::user()->can('create', 'DuncanMcClean\SimpleCommerce\Contracts\Discounts\Discount'),
    ]
)

@stop
