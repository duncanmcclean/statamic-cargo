@extends('statamic::layout')
@section('title', __('Create Tax Class'))

@section('content')
    <tax-class-create-form route="{{ cp_route('cargo.tax-classes.store') }}"></tax-class-create-form>
@endsection
