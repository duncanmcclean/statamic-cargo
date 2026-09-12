<script setup>
import { computed } from 'vue';

const props = defineProps({
    metadata: Array,
});

const values = computed(() => Object.fromEntries(props.metadata.map((item) => [item.handle, item.value])));

const indexComponent = (item) => Statamic.$app.component(`${item.fieldtype}-fieldtype-index`);
</script>

<template>
    <tr class="border-b border-gray-200 dark:border-gray-700">
        <td colspan="4" class="pb-3">
            <dl class="-mx-3 grid grid-cols-[repeat(auto-fit,minmax(16rem,1fr))] gap-x-6 gap-y-3 rounded-lg bg-gray-50 px-3 py-3 dark:bg-gray-900/50">
                <div v-for="item in metadata" :key="item.handle" class="min-w-0">
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ item.display }}</dt>
                    <dd class="mt-1 text-sm text-gray-800 dark:text-gray-200">
                        <component
                            v-if="indexComponent(item)"
                            :is="indexComponent(item)"
                            :handle="item.handle"
                            :value="item.value"
                            :values
                        />
                        <span v-else class="whitespace-pre-line break-words" v-text="item.value" />
                    </dd>
                </div>
            </dl>
        </td>
    </tr>
</template>
