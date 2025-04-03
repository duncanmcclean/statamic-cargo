<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0">
            <data-list-table>
                <template #cell-name="{ row: taxClass, index }">
                    <a :href="taxClass.edit_url">{{ __(taxClass.name) }}</a>
                </template>
                <template #cell-handle="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template #actions="{ row: taxClass, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="taxClass.edit_url" />
                        <dropdown-item
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${taxClass.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${taxClass.id}`"
                                :resource="taxClass"
                                @deleted="removeRow(taxClass)">
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
import Listing from '@statamic/components/Listing.vue'

export default {
    mixins: [Listing],

    props: [
        'initialRows',
        'initialColumns',
    ],

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns
        }
    }
}
</script>