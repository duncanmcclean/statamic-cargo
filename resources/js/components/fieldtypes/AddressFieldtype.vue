<script setup>
import { computed } from 'vue';
import { Fieldtype } from '@statamic/cms';
import { PublishFields as Fields, PublishFieldsProvider as FieldsProvider } from '@statamic/cms/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const fieldPathPrefix = computed(() => {
    if (props.fieldPathPrefix) {
        return `${props.fieldPathPrefix}.${props.handle}`;
    }

    return props.handle;
});

const metaPathPrefix = computed(() => {
    if (props.fieldPathPrefix) {
        return `${props.fieldPathPrefix}.${props.handle}.meta`;
    }

    return `${props.handle}.meta`;
});
</script>

<template>
	<FieldsProvider :fields="meta.fields" :field-path-prefix :meta-path-prefix>
		<Fields />
	</FieldsProvider>
</template>
