<script setup>
import { Fieldtype } from '@statamic/cms';
import { Input } from '@statamic/cms/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);

const codeUpdated = (value) => update(value.toUpperCase());
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
