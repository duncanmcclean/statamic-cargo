<template>
    <Input
        class="uppercase font-mono"
        :focus="true"
        :autoselect="true"
        input-type="text"
        :disabled="isReadOnly"
        :copyable="true"
        :model-value="value"
        @update:model-value="updateDebounced"
        @keydown.native.space.prevent
    />
</template>

<script>
import { Fieldtype } from 'statamic';
import { Input } from '@statamic/ui';
import debounce from '@statamic/util/debounce.js';

export default {
    mixins: [Fieldtype],

    components: { Input },

    methods: {
        updateDebounced: debounce(function (value) {
            this.$emit('update:value', value.toUpperCase());
        }, 500),
    },
};
</script>
