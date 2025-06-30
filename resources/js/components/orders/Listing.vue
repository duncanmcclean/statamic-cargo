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

<script>
import { DropdownItem, Listing } from '@statamic/ui';

export default {
    components: {
        Listing,
        DropdownItem,
    },

    props: {
        actionUrl: String,
        sortColumn: String,
        sortDirection: String,
        columns: Array,
        filters: Array,
    },

    data() {
        return {
            preferencesPrefix: `cargo.orders`,
            requestUrl: cp_url(`orders`),
            items: null,
            page: null,
            perPage: null,
        };
    },

    methods: {
        requestComplete({ items, parameters, activeFilters }) {
            this.items = items;
            this.page = parameters.page;
            this.perPage = parameters.perPage;
        },
    },
};
</script>
