<script setup>
import { Icon, EmptyStateMenu, EmptyStateItem, DocsCallout } from '@statamic/cms/ui';
import useArchitecturalBackground from '../../architectural-background.js';

useArchitecturalBackground();

defineProps({
    createUrl: String,
    icon: String,
});
</script>

<template>
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <Icon :name="icon" class="size-5 text-gray-500" />
            {{ __('Tax Classes') }}
        </h1>
    </header>

    <EmptyStateMenu :heading="__('cargo::messages.tax_class_intro')">
        <EmptyStateItem
            :href="createUrl"
            :icon="icon"
            :heading="__('Create a Tax Class')"
            :description="__('Get started by creating your first tax class.')"
        />
    </EmptyStateMenu>

    <DocsCallout :topic="__('Tax Classes')" url="https://builtwithcargo.dev/docs/taxes#tax-classes" />
</template>