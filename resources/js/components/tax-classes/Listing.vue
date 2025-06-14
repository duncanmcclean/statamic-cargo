<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="rows" v-slot="{ filteredRows: rows }">
        <ui-panel>
            <data-list-table>
                <template #cell-name="{ row: taxClass }">
                    <a :href="taxClass.edit_url">{{ __(taxClass.name) }}</a>
                </template>
                <template #actions="{ row: taxClass }">
                    <Dropdown placement="left-start" class="me-3">
                        <DropdownMenu>
                            <DropdownItem :text="__('Edit')" icon="edit" :href="taxClass.edit_url" />
                            <DropdownItem :text="__('Delete')" icon="trash" variant="destructive" @click="$refs[`deleter_${taxClass.id}`].confirm()" />
                        </DropdownMenu>
                    </Dropdown>

                    <resource-deleter
                        :ref="`deleter_${taxClass.id}`"
                        :resource="taxClass"
                        @deleted="removeRow(taxClass)"
                    />
                </template>
            </data-list-table>
        </ui-panel>
    </data-list>
</template>

<script>
import Listing from '@statamic/components/Listing.vue';
import { Dropdown, DropdownMenu, DropdownItem } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        Dropdown,
        DropdownMenu,
        DropdownItem,
    },

    props: ['initialRows', 'initialColumns'],

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns,
        };
    },
};
</script>
