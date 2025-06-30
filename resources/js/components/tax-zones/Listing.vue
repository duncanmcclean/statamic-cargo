<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="rows" v-slot="{ filteredRows: rows }">
        <ui-panel>
            <data-list-table>
                <template #cell-name="{ row: taxZone }">
                    <a :href="taxZone.edit_url">{{ __(taxZone.name) }}</a>
                </template>
                <template #actions="{ row: taxZone }">
                    <Dropdown placement="left-start" class="me-3">
                        <DropdownMenu>
                            <DropdownItem :text="__('Edit')" icon="edit" :href="taxZone.edit_url" />
                            <DropdownItem
                                :text="__('Delete')"
                                icon="trash"
                                variant="destructive"
                                @click="$refs[`deleter_${taxZone.id}`].confirm()"
                            />
                        </DropdownMenu>
                    </Dropdown>

                    <resource-deleter
                        :ref="`deleter_${taxZone.id}`"
                        :resource="taxZone"
                        @deleted="removeRow(taxZone)"
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
