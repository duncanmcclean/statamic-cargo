<template>
    <div>
        <text-input
            :model-value="value"
            :prepend="meta.symbol"
            :is-read-only="readOnly"
            placeholder="00.00"
            @update:model-value="inputUpdated"
        />
    </div>
</template>

<script>
import { Fieldtype } from 'statamic';

export default {
    mixins: [Fieldtype],

    methods: {
        inputUpdated(value) {
            if (!this.config.debounce) {
              return this.update(value);
            }

            this.updateDebounced(value);
        },
    },
}
</script>
