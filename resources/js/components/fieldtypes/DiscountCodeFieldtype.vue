<template>
    <Input
        class="font-mono uppercase"
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
import { FieldtypeMixin } from 'statamic';
import { Input } from '@statamic/ui';
import debounce from '@statamic/util/debounce.js';

export default {
    mixins: [FieldtypeMixin],

    components: { Input },

    methods: {
        updateDebounced: debounce(function (value) {
            this.$emit('update:value', value.toUpperCase());
        }, 500),
    },
};
</script>
