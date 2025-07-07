<script setup>
import { DropdownItem, Listing } from '@statamic/ui';
import DiscountStatusIndicator from './DiscountStatusIndicator.vue';
import { ref } from 'vue';

const props = defineProps({
    actionUrl: String,
    sortColumn: String,
    sortDirection: String,
    columns: Array,
    filters: Array,
});

const preferencesPrefix = 'cargo.discounts';
const requestUrl = cp_url(`discounts`);
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
    <Listing
        ref="listing"
        :url="requestUrl"
        :columns="columns"
        :action-url="actionUrl"
        :sort-column="sortColumn"
        :sort-direction="sortDirection"
        :preferences-prefix="preferencesPrefix"
        :filters="filters"
        push-query
        @request-completed="requestComplete"
    >
        <template #cell-name="{ row: discount, isColumnVisible }">
            <a class="title-index-field" :href="discount.edit_url" @click.stop>
                <DiscountStatusIndicator v-if="!isColumnVisible('status')" :status="discount.status" />
                <span v-text="discount.title" />
            </a>
        </template>
        <template #cell-status="{ row: discount }">
            <DiscountStatusIndicator :status="discount.status" show-label :show-dot="false" />
        </template>
        <template #prepended-row-actions="{ row: discount }">
            <DropdownItem :text="__('Edit')" :href="discount.edit_url" icon="edit" v-if="discount.editable" />
        </template>
    </Listing>
</template>
