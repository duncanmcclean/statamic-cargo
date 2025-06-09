@use(DuncanMcClean\Cargo\Cargo)
@extends('statamic::layout')
@section('title', __('Create Tax Class'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <tax-class-create-form
        route="{{ cp_route('cargo.tax-classes.store') }}"
        icon="{{ Cargo::svg('tax-classes') }}"
    ></tax-class-create-form>
@endsection
