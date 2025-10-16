<script setup>
import { Head } from '@statamic/cms/inertia';
import { Header, Dropdown, DropdownMenu, DropdownItem, DocsCallout } from '@statamic/cms/ui';
import OrdersListing from '../../components/orders/Listing.vue';

defineProps({
    blueprint: Object,
    columns: Array,
    filters: Array,
    actionUrl: String,
    editBlueprintUrl: String,
    canEditBlueprint: Boolean,
});
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

        <OrdersListing
            sort-column="order_number"
            sort-direction="desc"
            :columns="columns"
            :filters="filters"
            :action-url="actionUrl"
        />

        <DocsCallout :topic="__('Orders')" url="https://builtwithcargo.dev/docs/orders" />
    </div>
</template>