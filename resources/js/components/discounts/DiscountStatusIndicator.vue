<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: false,
        default: 'active',
        validator: (value) => ['active', 'scheduled', 'expired'].includes(value),
    },
    showDot: { type: Boolean, default: true },
    showLabel: { type: Boolean, default: false },
});

const statusClass = computed(() => {
    const classes = {
        active: 'bg-green-600',
        scheduled: 'status-scheduled-dot',
        expired: 'bg-gray-400 dark:bg-dark-200',
    };
    return classes[props.status];
});

const label = computed(() => {
    const labels = {
        active: __('Active'),
        scheduled: __('Scheduled'),
        expired: __('Expired'),
    };
    return labels[props.status];
});
</script>

<template>
    <span class="discount-status-index-field flex items-center gap-2">
        <span v-if="showDot" class="size-2 rounded-full" :class="statusClass" v-tooltip="label" />
        <span v-if="showLabel" class="status-index-field select-none" :class="`status-${status}`" v-text="label" />
    </span>
</template>