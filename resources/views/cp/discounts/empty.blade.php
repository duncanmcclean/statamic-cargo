@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Discounts'))

@section('content')
    <x-statamic::empty-screen
        title="{{ __('Discounts') }}"
        description="{{ __('cargo::messages.discount_configure_intro') }}"
        svg="empty/content"
        button_text="{{ __('Create Discount') }}"
        button_url="{{ cp_route('cargo.discounts.create') }}"
    />
@stop
