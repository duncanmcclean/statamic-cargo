<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0">
            <data-list-table>
                <template #cell-name="{ row: taxZone, index }">
                    <a :href="taxZone.edit_url">{{ __(taxZone.name) }}</a>
                </template>
                <template #actions="{ row: taxZone, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="taxZone.edit_url" />
                        <dropdown-item
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${taxZone.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${taxZone.id}`"
                                :resource="taxZone"
                                @deleted="removeRow(taxZone)"
                            >
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
import Listing from '@statamic/components/Listing.vue';

export default {
    mixins: [Listing],

    props: ['initialRows', 'initialColumns'],

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns,
        };
    },
};
</script>
