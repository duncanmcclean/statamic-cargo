<script setup>
import { Listing, DropdownItem } from '@statamic/cms/ui';
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
        <template #cell-title="{ row: taxClass }">
            <a class="title-index-field" :href="taxClass.edit_url" @click.stop>
                <span v-text="taxClass.title" />
            </a>

            <resource-deleter :ref="`deleter_${taxClass.id}`" :resource="taxClass" reload />
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
