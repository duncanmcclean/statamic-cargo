<script setup>
import { Fieldtype } from '@statamic/cms';
import { Input } from '@statamic/cms/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);
defineExpose(expose);
</script>

<template>
    <Input
        :prepend="meta.symbol"
        :disabled="config.read_only || readOnly"
        :model-value="value"
        @update:model-value="update"
    />
</template>