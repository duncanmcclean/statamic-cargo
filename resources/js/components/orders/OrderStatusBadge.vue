<script setup>
import { Badge } from '@statamic/cms/ui';
import { computed } from 'vue';

const props = defineProps({
	status: String,
	size: { type: String, default: 'default' },
});

const colour = computed(() => {
	let colours = {
		payment_pending: 'default',
		payment_received: 'blue',
		shipped: 'green',
		returned: 'orange',
		cancelled: 'red',
	};

	return colours[props.status] ?? 'green';
});

const label = computed(() => {
	return Statamic.$config.get('orderStatuses')
		.find(status => status.value === props.status)
		.label;
});
</script>

<template>
	<Badge :text="label" :color="colour" :size variant="flat" pill />
</template>
