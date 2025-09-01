<script setup>
import { computed, ref, useTemplateRef } from 'vue';
import {
    Header,
    Button,
    PublishContainer,
    PublishTabs,
    Panel,
    PanelHeader,
    Heading,
    Card,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownSeparator,
} from '@statamic/cms/ui';
import OrderStatus from './OrderStatus.vue';
import { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks } from '@statamic/cms/save-pipeline';
import { ItemActions } from '@statamic/cms';
import { resetValuesFromResponse } from '@statamic/cms/temporary';

const props = defineProps({
    blueprint: Object,
    reference: String,
    initialTitle: String,
    initialValues: Object,
    initialExtraValues: Object,
    initialMeta: Object,
    initialReadOnly: Boolean,
    actions: Object,
    itemActions: Array,
    itemActionUrl: String,
    canEditBlueprint: Boolean,
});

const container = useTemplateRef('container');
const title = ref(props.initialTitle);
const values = ref(props.initialValues);
const extraValues = ref(props.initialExtraValues);
const meta = ref(props.initialMeta);
const readOnly = ref(props.initialReadOnly);
const errors = ref({});
const saving = ref(false);

function save() {
    new Pipeline()
        .provide({ container, errors, saving })
        .through([
            new BeforeSaveHooks('order'),
            new Request(props.actions.save, 'patch'),
            new AfterSaveHooks('order'),
        ])
        .then((response) => {
            Statamic.$toast.success('Saved');
        });
}

const isDirty = computed(() => Statamic.$dirty.has('order'));
const hasItemActions = computed(() => props.itemActions.length > 0);

function actionStarted() {
    saving.value = true;
}

function actionCompleted(successful = null, response) {
    saving.value = false;

    if (successful === false) return;

    Statamic.$events.$emit('reset-action-modals');

    if (response.success === false) {
        Statamic.$toast.error(response.message || __('Action failed'));
    } else {
        Statamic.$toast.success(response.message || __('Action completed'));
    }

    if (response.data) {
        props.itemActions.value = response.data.data.itemActions;

        container.value.store.setValues(resetValuesFromResponse(response.data.values, container.value.store));
        container.value.store.setExtraValues(response.data.extraValues);
    }
}
</script>

<template>
    <Header :title icon="shopping-cart">
        <ItemActions
            v-if="canEditBlueprint || hasItemActions"
            :item="values.id"
            :url="itemActionUrl"
            :actions="itemActions"
            :is-dirty="isDirty"
            @started="actionStarted"
            @completed="actionCompleted"
            v-slot="{ actions: itemActions }"
        >
            <Dropdown>
                <template #trigger>
                    <Button icon="ui/dots" variant="ghost" />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-if="canEditBlueprint"
                        :text="__('Edit Blueprint')"
                        icon="blueprint-edit"
                        :href="actions.editBlueprint"
                    />
                    <DropdownSeparator v-if="canEditBlueprint && itemActions.length" />
                    <DropdownItem
                        v-for="action in itemActions"
                        :key="action.handle"
                        :text="__(action.title)"
                        :icon="action.icon"
                        :variant="action.dangerous ? 'destructive' : 'default'"
                        @click="action.run"
                    />
                </DropdownMenu>
            </Dropdown>
        </ItemActions>

        <Button variant="primary" text="Save" @click="save" :disabled="saving" />
    </Header>

    <PublishContainer
        ref="container"
        name="order"
        :blueprint="blueprint"
        :reference="reference"
        v-model="values"
        :extra-values="extraValues"
        :meta="meta"
        :errors="errors"
        :read-only="readOnly"
    >
        <PublishTabs>
            <template #actions>
                <Panel>
                    <PanelHeader>
                        <Heading :text="__('Order Status')" />
                    </PanelHeader>
                    <Card>
                        <OrderStatus
                            :order-id="values.id"
                            :statuses="meta.status.options"
                            :packing-slip-url="actions.packingSlip"
                            :model-value="values.status"
                            :tracking-number="values.tracking_number"
                            @update:modelValue="values.status = $event"
                            @update:trackingNumber="values.tracking_number = $event"
                        />
                    </Card>
                </Panel>
            </template>
        </PublishTabs>
    </PublishContainer>
</template>
