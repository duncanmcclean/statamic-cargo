<script setup>
import { Head, Link } from '@statamic/cms/inertia';
import { Header, Button, Listing, DropdownItem, DocsCallout } from '@statamic/cms/ui';
import { ref } from 'vue';

const props = defineProps({
    taxZones: Array,
    columns: Array,
    createUrl: String,
});

const items = ref(props.taxZones);

const deleted = (item) => {
    items.value = items.value.filter(i => i.id !== item.id);
};
</script>

<template>
    <Head :title="__('Tax Zones')" />

    <div class="max-w-5xl mx-auto">
        <Header :title="__('Tax Zones')" icon="map-search">
            <Button :href="createUrl" :text="__('Create Tax Zone')" variant="primary" />
        </Header>

        <Listing :items :columns :allow-customizing-columns="false" :allow-search="false">
            <template #cell-title="{ row: taxZone }">
                <Link class="title-index-field" :href="taxZone.edit_url">
                    <span v-text="taxZone.title" />
                </Link>

                <resource-deleter :ref="`deleter_${taxZone.id}`" :resource="taxZone" @deleted="deleted(taxZone)" />
            </template>
            <template #prepended-row-actions="{ row: taxZone }">
                <DropdownItem :text="__('Edit')" icon="edit" :href="taxZone.edit_url" />
                <DropdownItem
                    :text="__('Delete')"
                    icon="trash"
                    variant="destructive"
                    @click="$refs[`deleter_${taxZone.id}`].confirm()"
                />
            </template>
        </Listing>

        <DocsCallout :topic="__('Tax Zones')" url="https://builtwithcargo.dev/docs/taxes#tax-zones" />
    </div>
</template>