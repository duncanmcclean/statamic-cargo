<script setup>
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@statamic/cms/ui';
import { useArchitecturalBackground } from '@statamic/cms/interia';

useArchitecturalBackground();

defineProps({
    createUrl: String,
});
</script>

<template>
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <Icon name="shopping-store-discount-percent" class="size-5 text-gray-500" />
            {{ __('Discounts') }}
        </h1>
    </header>

    <EmptyStateMenu :heading="__('cargo::messages.discount_configure_intro')">
        <EmptyStateItem
            :href="createUrl"
            icon="shopping-store-discount-percent"
            :heading="__('Create a Discount')"
            :description="__('Get started by creating your first discount.')"
        />
    </EmptyStateMenu>

    <DocsCallout :topic="__('Discounts')" url="https://builtwithcargo.dev/docs/discounts" />
</template>