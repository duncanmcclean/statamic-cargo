<script setup>
import { Fieldtype, DateFormatter } from '@statamic/cms';
import { Heading, Subheading, Avatar, Table, TableRows, TableRow, TableCell } from '@statamic/cms/ui';
import { ref } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const events = ref(props.value);

const formatRelativeDate = (value) => {
    const isToday = new Date(value * 1000) < new Date().setUTCHours(0, 0, 0, 0);

    return !isToday
        ? __('Today')
        : DateFormatter.format(value * 1000, {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        });
};

const formatTime = (date) => DateFormatter.format(date * 1000, 'time');

// todo: temp
const selected = ref([]);

const toggleSelection = (event) => {
    if (selected.value.includes(event.id)) {
        selected.value = selected.value.filter((id) => id !== event.id);
        return;
    }

    selected.value.push(event.id);
};
</script>

<template>
    <div class="flex flex-col gap-y-6">
        <div v-for="group in events" :key="group.day">
            <Heading size="sm" class="mb-1 text-gray-600 dark:text-gray-300" v-text="formatRelativeDate(group.day)" />
            <div class="relative grid">
                <div class="absolute inset-y-0 left-3 top-3 border-l-1 border-gray-400 dark:border-gray-600 border-dashed" />
                <div
                    v-for="event in group.events"
                    class="relative block py-2"
                >
                    <div class="flex gap-3">
                        <Avatar v-if="event.user" :user="event.user" class="size-6 shrink-0 mt-1" />

                        <div class="grid gap-1 w-full">
                            <button
                                class="flex items-center gap-2"
                                :class="{ 'cursor-pointer': event.metadata }"
                                :aria-label="__('Click to view metadata')"
                                :title="__('Click to view metadata')"
                                @click="toggleSelection(event)"
                            >
                                <div class="font-medium" v-text="event.title" />
                            </button>
                            <Subheading class="text-xs text-gray-500! dark:text-gray-400!" :class="{ 'text-gray-800! dark:text-white!': false }">
                                {{ formatTime(event.date) }}
                                <template v-if="event.user">
                                    by {{ event.user.name || event.user.email }}
                                </template>
                            </Subheading>

                            <div v-if="event.metadata && selected.includes(event.id)" class="border rounded-md py-1 px-2 mt-2 mb-4">
                                <Table class="w-full">
                                    <TableRows>
                                        <TableRow v-for="(value, key) in event.metadata">
                                            <TableCell class="font-medium" v-html="key" />
                                            <TableCell v-html="value" />
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