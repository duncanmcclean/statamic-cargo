<script setup>
import { Head } from '@statamic/cms/inertia';
import { Header, Button, DocsCallout } from '@statamic/cms/ui';
import TaxClassListing from '../../components/tax-classes/Listing.vue';

defineProps({
    taxClasses: Array,
    columns: Array,
    createUrl: String,
    icon: String,
});
</script>

<template>
    <Head :title="__('Tax Classes')" />

    <div class="max-w-5xl mx-auto">
        <Header :title="__('Tax Classes')" :icon="icon">
            <Button :href="createUrl" :text="__('Create Tax Class')" variant="primary" />
        </Header>

        <TaxClassListing :initial-items="taxClasses" :initial-columns="columns" />

        <DocsCallout :topic="__('Tax Classes')" url="https://builtwithcargo.dev/docs/taxes#tax-classes" />
    </div>
</template>