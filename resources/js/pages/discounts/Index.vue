<script setup>
import { Head } from '@statamic/cms/inertia';
import { Header, Button, DocsCallout } from '@statamic/cms/ui';
import DiscountsListing from '../../components/discounts/Listing.vue';

defineProps({
    blueprint: Object,
    columns: Array,
    filters: Array,
    createUrl: String,
    actionUrl: String,
});
</script>

<template>
    <Head :title="__('Discounts')" />

    <Header :title="__('Discounts')" icon="shopping-store-discount-percent">
        <Button :href="createUrl" :text="__('Create Discount')" variant="primary" />
    </Header>

    <DiscountsListing
        sort-column="code"
        sort-direction="asc"
        :columns
        :filters
        :action-url
    />

    <DocsCallout :topic="__('Discounts')" url="https://builtwithcargo.dev/docs/discounts" />
</template>