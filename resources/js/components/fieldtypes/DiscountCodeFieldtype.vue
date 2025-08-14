<script setup>
import { Fieldtype } from 'statamic';
import { Input } from '@statamic/ui';

const emit = defineEmits(Fieldtype.emits);

const codeUpdated = (value) => emit('update:value', value.toUpperCase());
</script>

<template>
    <Input
        class="font-mono uppercase"
        :autoselect="true"
        input-type="text"
        :disabled="isReadOnly"
        :copyable="true"
        :model-value="value"
        @update:model-value="codeUpdated"
        @keydown.native.space.prevent
    />
</template>
