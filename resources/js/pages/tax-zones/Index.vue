<script setup>
import { Head } from '@statamic/cms/inertia';
import { Header, Button, DocsCallout } from '@statamic/cms/ui';
import TaxZoneListing from '../../components/tax-zones/Listing.vue';

defineProps({
    taxZones: Array,
    columns: Array,
    createUrl: String,
});
</script>

<template>
    <Head :title="__('Tax Zones')" />

    <div class="max-w-5xl mx-auto">
        <Header :title="__('Tax Zones')" icon="map-search">
            <Button :href="createUrl" :text="__('Create Tax Zone')" variant="primary" />
        </Header>

        <TaxZoneListing :initial-items="taxZones" :initial-columns="columns" />

        <DocsCallout :topic="__('Tax Zones')" url="https://builtwithcargo.dev/docs/taxes#tax-zones" />
    </div>
</template>