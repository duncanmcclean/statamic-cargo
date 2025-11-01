<script setup>
import { Head } from '@statamic/cms/inertia';
import OrderPublishForm from '../../components/orders/PublishForm.vue';

defineProps({
    blueprint: Object,
    reference: String,
    title: String,
    actions: Object,
    values: Object,
    extraValues: Object,
    meta: Object,
    readOnly: Boolean,
    itemActions: Array,
    itemActionUrl: String,
    canEditBlueprint: Boolean,
});
</script>

<template>
    <Head :title />

    <OrderPublishForm
        :blueprint
        :reference
        :initial-title="title"
        :initial-values="values"
        :initial-extra-values="extraValues"
        :initial-meta="meta"
        :initial-read-only="readOnly"
        :actions
        :item-actions
        :item-action-url
        :can-edit-blueprint
    />
</template>