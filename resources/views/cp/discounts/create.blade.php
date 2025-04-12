@extends('statamic::layout')
@section('title', $title)

@section('content')
    <base-discount-create-form
        title="{{ $title }}"
        :actions="{{ json_encode($actions) }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        create-another-url="{{ cp_route('cargo.discounts.create') }}"
        listing-url="{{ cp_route('cargo.discounts.index') }}"
    ></base-discount-create-form>
@endsection
