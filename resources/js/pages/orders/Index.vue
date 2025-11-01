<script setup>
import { Head, Link } from '@statamic/cms/inertia';
import { Header, Dropdown, DropdownMenu, DropdownItem, Listing, DocsCallout } from '@statamic/cms/ui';
import { ref } from 'vue';

defineProps({
    blueprint: Object,
    columns: Array,
    filters: Array,
    actionUrl: String,
    editBlueprintUrl: String,
    canEditBlueprint: Boolean,
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
    <Head :title="__('Orders')" />

    <div class="max-w-full mx-auto">
        <Header :title="__('Orders')" icon="shopping-cart">
            <Dropdown v-if="canEditBlueprint" placement="left-start" class="me-2">
                <DropdownMenu>
                    <DropdownItem
                        :text="__('Edit Blueprint')"
                        icon="blueprint-edit"
                        :href="editBlueprintUrl"
                    />
                </DropdownMenu>
            </Dropdown>
        </Header>

        <Listing
            :url="cp_url('orders')"
            :columns
            :action-url
            sort-column="order_number"
            sort-direction="desc"
            preferences-prefix="cargo.orders"
            :filters
            push-query
            @request-completed="requestComplete"
        >
            <template #cell-order_number="{ row: order }">
                <Link class="order-number-index-field" :href="order.edit_url">
                    <span v-text="`#${order.order_number}`" />
                </Link>
            </template>
            <template #prepended-row-actions="{ row: order }">
                <DropdownItem :text="__('Edit')" :href="order.edit_url" icon="edit" v-if="order.editable" />
            </template>
        </Listing>

        <DocsCallout :topic="__('Orders')" url="https://builtwithcargo.dev/docs/orders" />
    </div>
</template>