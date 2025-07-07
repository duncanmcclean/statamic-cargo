<script setup>
import { IndexFieldtype } from 'statamic';
import { Badge } from '@statamic/ui';
import { computed } from 'vue';

const props = defineProps(IndexFieldtype.props);

const color = computed(() => {
    let colors = {
        payment_pending: 'default',
        payment_received: 'blue',
        shipped: 'green',
        returned: 'orange',
        cancelled: 'red',
    };

    return colors[props.value.value] ?? 'green';
});
</script>

<template>
    <Badge :text="String(value.label)" :color variant="flat" pill />
</template>
