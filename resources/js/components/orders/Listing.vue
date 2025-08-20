<script setup>
import { DropdownItem, Listing } from '@statamic/cms/ui';
import { ref } from 'vue';

const props = defineProps({
    actionUrl: String,
    sortColumn: String,
    sortDirection: String,
    columns: Array,
    filters: Array,
});

const preferencesPrefix = 'cargo.orders';
const requestUrl = cp_url(`orders`);
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
        <template #cell-order_number="{ row: order, isColumnVisible }">
            <a class="order-number-index-field" :href="order.edit_url" @click.stop>
                <span v-text="`#${order.order_number}`" />
            </a>
        </template>
        <template #prepended-row-actions="{ row: order }">
            <DropdownItem :text="__('Edit')" :href="order.edit_url" icon="edit" v-if="order.editable" />
        </template>
    </Listing>
</template>
