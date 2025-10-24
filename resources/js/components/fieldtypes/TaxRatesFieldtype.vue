<script setup>
import { Fieldtype } from '@statamic/cms';
import { PublishFields as Fields, PublishFieldsProvider as FieldsProvider } from '@statamic/cms/ui';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);
</script>

<template>
    <div class="!-my-4 !-mx-4.5">
        <FieldsProvider
            :fields="config.fields"
            :field-path-prefix="fieldPathPrefix ? `${fieldPathPrefix}.${handle}` : handle"
            :meta-path-prefix="metaPathPrefix ? `${metaPathPrefix}.${handle}` : handle"
        >
            <Fields />
        </FieldsProvider>
    </div>
</template>