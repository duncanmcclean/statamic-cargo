@extends('statamic::layout')
@section('title', __('Coupons'))

@section('content')

@include(
    'statamic::partials.empty-state',
    [
        'title' => __('Coupons'),
        'description' => __('cargo::messages.coupon_configure_intro'),
        'svg' => 'empty/fieldsets',
        'button_text' => __('Create Coupon'),
        'button_url' => cp_route('cargo.coupons.create'),
        'can' => Auth::user()->can('create', 'DuncanMcClean\SimpleCommerce\Contracts\Coupons\Coupon'),
    ]
)

@stop
