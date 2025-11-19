<script setup>
import { Head, Link } from '@statamic/cms/inertia';
import { Header, Button, Listing, DropdownItem, DocsCallout } from '@statamic/cms/ui';
import { ref } from 'vue';

const props = defineProps({
    taxClasses: Array,
    columns: Array,
    createUrl: String,
    icon: String,
});

const items = ref(props.taxClasses);

const deleted = (item) => {
    items.value = items.value.filter(i => i.id !== item.id);
};
</script>

<template>
    <Head :title="__('Tax Classes')" />

    <div class="max-w-5xl mx-auto">
        <Header :title="__('Tax Classes')" :icon="icon">
            <Button :href="createUrl" :text="__('Create Tax Class')" variant="primary" />
        </Header>

        <Listing :items :columns :allow-customizing-columns="false" :allow-search="false">
            <template #cell-title="{ row: taxClass }">
                <Link class="title-index-field" :href="taxClass.edit_url">
                    <span v-text="taxClass.title" />
                </Link>

                <resource-deleter :ref="`deleter_${taxClass.id}`" :resource="taxClass" @deleted="deleted(taxClass)" />
            </template>
            <template #prepended-row-actions="{ row: taxClass }">
                <DropdownItem :text="__('Edit')" icon="edit" :href="taxClass.edit_url" />
                <DropdownItem
                    :text="__('Delete')"
                    icon="trash"
                    variant="destructive"
                    @click="$refs[`deleter_${taxClass.id}`].confirm()"
                />
            </template>
        </Listing>

        <DocsCallout :topic="__('Tax Classes')" url="https://builtwithcargo.dev/docs/taxes#tax-classes" />
    </div>
</template>