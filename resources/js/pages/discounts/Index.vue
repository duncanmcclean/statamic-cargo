<script setup>
import { Head, Link } from '@statamic/cms/inertia';
import { Header, Button, Listing, DropdownItem, DocsCallout } from '@statamic/cms/ui';
import DiscountStatusIndicator from '../../components/discounts/DiscountStatusIndicator.vue';
import { ref } from 'vue';

defineProps({
    blueprint: Object,
    columns: Array,
    filters: Array,
    createUrl: String,
    actionUrl: String,
});

const items = ref(null);
const page = ref(null);
const perPage = ref(null);

function requestComplete({ items: newItems, parameters }) {
    items.value = newItems;
    page.value = parameters.page;
    perPage.value = parameters.perPage;
}
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

    <Listing
        ref="listing"
        :url="cp_url(`discounts`)"
        :columns
        :action-url
        sort-column="title"
        sort-direction="asc"
        preferences-prefix="cargo.discounts"
        :filters
        push-query
        @request-completed="requestComplete"
    >
        <template #cell-title="{ row: discount, isColumnVisible }">
            <Link class="title-index-field" :href="discount.edit_url">
                <DiscountStatusIndicator v-if="!isColumnVisible('status')" :status="discount.status" />
                <span v-text="discount.title" />
            </Link>
        </template>
        <template #cell-status="{ row: discount }">
            <DiscountStatusIndicator :status="discount.status" show-label :show-dot="false" />
        </template>
        <template #prepended-row-actions="{ row: discount }">
            <DropdownItem :text="__('Edit')" :href="discount.edit_url" icon="edit" v-if="discount.editable" />
        </template>
    </Listing>

    <DocsCallout :topic="__('Discounts')" url="https://builtwithcargo.dev/docs/discounts" />
</template>