<script setup>
import { Head } from '@statamic/cms/inertia';
import DiscountPublishForm from '../../components/discounts/PublishForm.vue';

defineProps({
    blueprint: Object,
    title: String,
    values: Object,
    meta: Object,
    submitUrl: String,
    createAnotherUrl: String,
    listingUrl: String,
});
</script>

<template>
    <Head :title />

    <DiscountPublishForm
        :title
        :blueprint
        :initial-values="values"
        :initial-meta="meta"
        :submit-url
        submit-method="post"
        :create-another-url
        :listing-url
    />
</template>
