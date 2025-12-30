<script setup>
import { Fieldtype, DateFormatter } from '@statamic/cms';
import { Heading, Subheading, Avatar, Table, TableRows, TableRow, TableCell, Icon } from '@statamic/cms/ui';
import { ref } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const events = ref(props.value);

const formatRelativeDate = (value) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const isToday = value === Math.floor(today.getTime() / 1000);

    return isToday
        ? __('Today')
        : DateFormatter.format(value * 1000, {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        });
};

const formatTime = (date) => DateFormatter.format(date * 1000, 'time');

const selected = ref([]);

const toggleSelection = (event) => {
    if (selected.value.includes(event.id)) {
        selected.value = selected.value.filter((id) => id !== event.id);
        return;
    }

	if (event.metadata.length === 0) return;

    selected.value.push(event.id);
};
</script>

<template>
    <div class="flex flex-col gap-y-6">
        <div v-for="group in events" :key="group.day">
            <Heading size="sm" class="mb-1 text-gray-600 dark:text-gray-300" v-text="formatRelativeDate(group.day)" />

            <div class="relative grid">
                <div class="absolute inset-y-0 left-3 top-3 border-l-1 border-gray-400 dark:border-gray-600 border-dashed" />

                <div v-for="event in group.events" class="relative block py-2">
                    <div class="flex gap-3">
                        <Avatar v-if="event.user" :user="event.user" class="size-6 shrink-0 mt-1" />

	                    <div v-else class="size-6 flex items-center justify-center shrink-0 bg-white dark:bg-gray-900 rounded-full p-1 border border-gray-200 dark:border-white/15">
		                    <Icon :name="meta.cargoMark" class="w-full" />
	                    </div>

                        <div class="grid gap-1 w-full">
                            <button
                                class="flex items-center gap-1"
                                :class="{ 'cursor-pointer': event.metadata.length != 0 }"
                                @click="toggleSelection(event)"
                            >
                                <Heading class="font-medium" v-text="event.message" />
	                            <Icon
		                            v-if="event.metadata.length != 0"
		                            name="chevron-down"
		                            class="text-gray-400 dark:text-white/40"
		                            :class="{ 'rotate-180': selected.includes(event.id) }"
	                            />
                            </button>

                            <Subheading
	                            class="text-xs text-gray-500! dark:text-gray-400!"
	                            :class="{ 'text-gray-800! dark:text-white!': false }"
                            >
                                {{ formatTime(event.timestamp) }}
                                <template v-if="event.user">
                                    by {{ event.user.name || event.user.email }}
                                </template>
                            </Subheading>

                            <div
	                            v-if="event.metadata && selected.includes(event.id)"
	                            class="border border-gray-200 dark:border-white/15 rounded-md py-0.5 px-2 mt-2 mb-4"
                            >
                                <Table class="w-full">
                                    <TableRows>
                                        <TableRow v-for="(value, key) in event.metadata">
                                            <TableCell class="font-medium text-sm" v-text="__(key)" />
                                            <TableCell class="break-all text-sm" v-text="__(value)" />
                                        </TableRow>
                                    </TableRows>
                                </Table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>