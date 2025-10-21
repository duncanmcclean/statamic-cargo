<script setup>
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@statamic/cms/ui';
import useArchitecturalBackground from '../../architectural-background.js';

useArchitecturalBackground();

defineProps({
    createUrl: String,
});
</script>

<template>
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <Icon name="map-search" class="size-5 text-gray-500" />
            {{ __('Tax Zones') }}
        </h1>
    </header>

    <EmptyStateMenu :heading="__('cargo::messages.tax_zones_intro')">
        <EmptyStateItem
            :href="createUrl"
            icon="map-search"
            :heading="__('Create a Tax Zone')"
            :description="__('Get started by creating your first tax zone.')"
        />
    </EmptyStateMenu>

    <DocsCallout :topic="__('Tax Zones')" url="https://builtwithcargo.dev/docs/taxes#tax-zones" />
</template>