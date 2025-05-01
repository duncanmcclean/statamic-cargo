<template>
    <Badge :text="String(value.label)" :color="color" variant="flat" pill />
</template>

<script>
import { IndexFieldtype } from 'statamic';
import { Badge } from '@statamic/ui';

export default {
    mixins: [IndexFieldtype],

    components: {
        Badge,
    },

    computed: {
        color() {
            let colors = {
                payment_pending: 'default',
                payment_received: 'blue',
                shipped: 'green',
                returned: 'orange',
                cancelled: 'red',
            };

            return colors[this.value.value] ?? 'green';
        },
    },
};
</script>
