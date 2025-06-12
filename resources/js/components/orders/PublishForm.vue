<script setup>
import { computed, ref, useTemplateRef } from 'vue';
import { SavePipeline } from 'statamic';
import { Header, Button, PublishContainer, PublishTabs, Panel, PanelHeader, Heading, Card, Dropdown, DropdownMenu, DropdownItem, DropdownSeparator } from '@statamic/ui';
import OrderStatus from './OrderStatus.vue';
const { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks } = SavePipeline;
import ItemActions from '@statamic/components/actions/ItemActions.vue';

// todo: implement actions
// todo: implement read only

const props = defineProps({
    blueprint: Object,
    icon: String,
    initialTitle: String,
    initialValues: Object,
    initialMeta: Object,
    initialReadOnly: Boolean,
    actions: Object,
    itemActions: Array,
    itemActionUrl: String,
    packingSlipUrl: String,
    canEditBlueprint: Boolean,
});

const container = useTemplateRef('container');
const title = ref(props.initialTitle);
const values = ref(props.initialValues);
const meta = ref(props.initialMeta);
const errors = ref({});
const saving = ref(false);

function save() {
    new Pipeline()
        .provide({ container, errors, saving })
        .through([
            new BeforeSaveHooks('order'),
            new Request(props.actions.save, 'patch', { values: values.value }),
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
        props.itemActions.value = response.data.itemActions; // todo: does this need to be initial?
    }
}
</script>

<template>
    <Header :title :icon>
        <ItemActions
            v-if="canEditBlueprint || hasItemActions"
            :item="values.id"
            :url="itemActionUrl"
            :actions="itemActions"
            :is-dirty="isDirty"
            @started="actionStarted"
            @completed="actionCompleted"
            v-slot="{ actions }"
        >
            <Dropdown>
                <template #trigger>
                    <Button icon="ui/dots" variant="ghost" />
                </template>
                <DropdownMenu>
                    <DropdownItem :text="__('Edit Blueprint')" icon="blueprint-edit" v-if="canEditBlueprint" :href="actions.editBlueprint" />
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
        :values="values"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
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
                            :packing-slip-url="packingSlipUrl"
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