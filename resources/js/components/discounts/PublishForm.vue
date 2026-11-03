<script setup>
import { onMounted, onUnmounted, ref, useTemplateRef } from 'vue';
import { router } from '@statamic/cms/inertia';
import { Header, Button, PublishContainer, PublishTabs } from '@statamic/cms/ui';
import { Pipeline, Request } from '@statamic/cms/save-pipeline';
import { SaveButtonOptions } from '@statamic/cms';

const props = defineProps({
    title: String,
    blueprint: Object,
    initialValues: Object,
    initialMeta: Object,
    submitUrl: String,
    submitMethod: { type: String, default: 'patch' },
    createAnotherUrl: String,
    listingUrl: String,
});

const container = useTemplateRef('container');
const values = ref(props.initialValues);
const meta = ref(props.initialMeta);
const errors = ref({});
const saving = ref(false);

function save({ quickSave = false } = {}) {
    new Pipeline()
        .provide({ container, errors, saving })
        .through([new Request(props.submitUrl, props.submitMethod)])
        .then((response) => {
            Statamic.$toast.success(__('Saved'));

            const afterSaveOption = Statamic.$preferences.get('cargo.discounts.after_save') ?? 'listing';
            const nextAction = quickSave ? 'continue_editing' : afterSaveOption;

            if (nextAction === 'create_another') {
                router.get(props.createAnotherUrl);
            } else if (nextAction === 'listing') {
                router.get(props.listingUrl);
            } else if (response.data.redirect) {
                router.get(response.data.redirect);
            }
        });
}

let saveKeyBinding;
let quickSaveKeyBinding;

onMounted(() => {
    saveKeyBinding = Statamic.$keys.bindGlobal(['mod+return'], (e) => {
        e.preventDefault();
        save();
    });

    quickSaveKeyBinding = Statamic.$keys.bindGlobal(['mod+s'], (e) => {
        e.preventDefault();
        save({ quickSave: true });
    });
});

onUnmounted(() => {
    saveKeyBinding.destroy();
    quickSaveKeyBinding.destroy();
});
</script>

<template>
    <Header :title icon="shopping-store-discount-percent">
        <SaveButtonOptions preferences-prefix="cargo.discounts">
            <Button variant="primary" :text="__('Save')" @click="save()" :disabled="saving" />
        </SaveButtonOptions>
    </Header>

    <PublishContainer
        ref="container"
        name="discount"
        :blueprint
        v-model="values"
        :meta
        :errors
        :remember-tab="true"
    >
        <PublishTabs />
    </PublishContainer>
</template>
