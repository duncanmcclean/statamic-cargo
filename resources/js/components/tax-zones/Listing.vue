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
</template>