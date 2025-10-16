<script setup>
import { Link } from '@statamic/cms/inertia';
import { Listing, DropdownItem } from '@statamic/cms/ui';
import { ref } from 'vue';

const props = defineProps({
    initialItems: Array,
    initialColumns: Array,
});

const items = ref(props.initialItems);
const columns = ref(props.initialColumns);

const deleted = (item) => {
    items.value = items.value.filter(i => i.id !== item.id);
};
</script>

<template>
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
</template>
