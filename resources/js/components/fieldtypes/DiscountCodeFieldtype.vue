<template>
    <text-fieldtype
        :handle="config.handle"
        :meta="{}"
        :config="{
            classes: 'uppercase font-mono',
            focus: true,
            autoselect: true,
            input_type: 'text',
        }"
        :value="value"
        @update:value="updateDebounced"
        @keydown.native.space.prevent
    />
</template>

<script>
import { Fieldtype } from 'statamic';
import debounce from '@statamic/util/debounce.js';

export default {
    mixins: [Fieldtype],

    methods: {
        updateDebounced: debounce(function (value) {
            this.$emit('update:value', value.toUpperCase());
        }, 500),
    },
};
</script>
