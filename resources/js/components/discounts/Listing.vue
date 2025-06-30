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
                <span v-text="discount.name" />
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

<script>
import { DropdownItem, Listing } from '@statamic/ui';
import DiscountStatusIndicator from '../DiscountStatusIndicator.vue';

export default {
    components: {
        Listing,
        DropdownItem,
        DiscountStatusIndicator,
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
            preferencesPrefix: `cargo.discounts`,
            requestUrl: cp_url(`discounts`),
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
