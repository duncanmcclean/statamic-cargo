<script setup>
import { Modal, Button, RadioGroup, Radio, CheckboxGroup, Checkbox } from '@statamic/cms/ui';
import { ref, computed } from 'vue';

const emit = defineEmits(['close']);

const props = defineProps({
    url: { type: String, required: true },
    columns: { type: Array, required: true },
    listingParameters: { type: Object, default: () => ({}) },
});

const scope = ref('all');
const selectedColumns = ref(props.columns.map((column) => column.handle));

const hasFilteredScope = computed(() => !!(props.listingParameters.search || props.listingParameters.filters));
const allColumnsSelected = computed(() => selectedColumns.value.length === props.columns.length);

function toggleAllColumns() {
    selectedColumns.value = allColumnsSelected.value ? [] : props.columns.map((column) => column.handle);
}

function exportOrders() {
    const { sort, order, search, filters } = props.listingParameters;
    const query = new URLSearchParams();

    if (sort) query.set('sort', sort);
    if (order) query.set('order', order);

    if (scope.value === 'filtered') {
        if (search) query.set('search', search);
        if (filters) query.set('filters', filters);
    }

    if (!allColumnsSelected.value) {
        query.set('columns', selectedColumns.value.join(','));
    }

    window.open(query.size ? `${props.url}?${query}` : props.url, '_blank');
    emit('close');
}
</script>

<template>
    <Modal :title="__('Export Orders')" open @update:open="emit('close')">
        <div class="space-y-6">
            <div>
                <label class="text-sm font-medium mb-1.5 block">{{ __('Orders') }}</label>
                <RadioGroup v-model="scope">
                    <Radio value="all" :label="__('All Orders')" />
                    <Radio
                        value="filtered"
                        :label="__('Filtered Orders')"
                        :description="__('Only export orders matching the current search and filters.')"
                        :disabled="!hasFilteredScope"
                    />
                </RadioGroup>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-sm font-medium block">{{ __('Columns') }}</label>
                    <button
                        type="button"
                        class="cursor-pointer text-xs text-gray-500 hover:text-gray-800 dark:hover:text-gray-200"
                        @click="toggleAllColumns"
                    >
                        {{ allColumnsSelected ? __('Deselect All') : __('Select All') }}
                    </button>
                </div>
                <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                    <CheckboxGroup v-model="selectedColumns">
                        <Checkbox v-for="column in columns" :key="column.handle" :value="column.handle" :label="column.title" />
                    </CheckboxGroup>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end p-2">
                <Button variant="primary" :text="__('Export')" :disabled="!selectedColumns.length" @click="exportOrders" />
            </div>
        </template>
    </Modal>
</template>
