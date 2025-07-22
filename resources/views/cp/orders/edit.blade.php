@use(Statamic\CP\Breadcrumbs\Breadcrumbs)

@extends('statamic::layout')
@section('title', Breadcrumbs::title($title))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <order-publish-form
        :blueprint="{{ json_encode($blueprint) }}"
        icon="{{ $icon }}"
        reference="{{ $reference }}"
        initial-title="{{ $title }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-extra-values="{{ json_encode($extraValues) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-read-only="{{ json_encode($readOnly) }}"
        :actions="{{ json_encode($actions) }}"
        :item-actions="{{ json_encode($itemActions) }}"
        item-action-url="{{ $itemActionUrl }}"
        :can-edit-blueprint="{{ json_encode($canEditBlueprint) }}"
    ></order-publish-form>
@endsection
