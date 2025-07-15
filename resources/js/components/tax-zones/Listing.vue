<script setup>
import { Listing, DropdownItem } from '@statamic/ui';
import { ref } from 'vue';

const props = defineProps({
    initialItems: Array,
    initialColumns: Array,
});

const items = ref(props.initialItems);
const columns = ref(props.initialColumns);
</script>

<template>
    <Listing :items :columns :allow-customizing-columns="false" :allow-search="false">
        <template #cell-title="{ row: taxZone }">
            <a class="title-index-field" :href="taxZone.edit_url" @click.stop>
                <span v-text="taxZone.title" />
            </a>

            <resource-deleter :ref="`deleter_${taxZone.id}`" :resource="taxZone" reload />
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