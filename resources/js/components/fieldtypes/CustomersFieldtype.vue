<script setup>
import { Fieldtype, InlineEditForm, requireElevatedSession } from '@statamic/cms';
import { Dropdown, DropdownMenu, DropdownItem, Heading, Description, Badge, injectPublishContext } from '@statamic/cms/ui';
import { getCurrentInstance, computed, ref } from 'vue';

const instance = getCurrentInstance();
const { $axios } = instance.appContext.config.globalProperties;

const { values, parentContainer: initialParentContainer } = injectPublishContext();

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const name = computed(() => {
    if (props.value.first_name && props.value.last_name) {
        return `${props.value.first_name} ${props.value.last_name}`;
    }

    return props.value.name;
})

const isEditingUser = ref(false);

function edit() {
    if (!props.value.editable) return;
    if (props.value.invalid) return;

    if (props.value.reference) {
        let parentContainer = initialParentContainer;

        while (parentContainer) {
            if (parentContainer.reference.value === props.value.reference) {
                Statamic.$toast.error(__("You're already editing this item."));
                return;
            }
            parentContainer = parentContainer.parentContainer;
        }
    }

    requireElevatedSession()
        .then(() => (isEditingUser.value = true))
        .catch(() => {});
}

function itemUpdated(responseData) {
    emit('update:value', {
        ...props.value,
        ...responseData.values,
    });
}

function convertToUser() {
    $axios
        .post(props.meta.convertGuestToUserUrl, {
            email: props.value.email,
            order_id: values.value.id,
        })
        .then((response) => {
            emit('update:value', response.data);
            Statamic.$toast.success(__('Guest has been converted to a user.'));
        })
        .catch((error) => {
            Statamic.$toast.error(error.response.data.message);
        });
}
</script>

<template>
    <div class="relationship-input @container h-full w-full">
        <div>
            <div
                class="shadow-ui-sm related-item relative z-2 mb-1.5 flex h-full w-full items-center gap-2 rounded-lg border border-gray-200 bg-white px-1.5 py-1.5 text-base last:mb-0 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black"
            >
                <div class="flex flex-1 items-center p-1">
                    <Heading v-if="value.invalid" v-tooltip="__('An item with this ID could not be found')">
                        {{ value.id }}
                        <Badge pill color="red" :text="__('Invalid')" />
                    </Heading>

                    <div v-else>
                        <Heading v-if="value.type === 'user' && value.editable">
                            <a :href="value.edit_url" @click.prevent="edit" v-text="name" />
                        </Heading>

                        <Heading v-else-if="value.type === 'guest'" class="truncate">
                            {{ name }}
                            <Badge pill size="sm" :text="__('Guest')" />
                        </Heading>

                        <Heading v-else :text="name" />
                        <Description v-if="value.email" :text="value.email" />
                    </div>

                    <InlineEditForm
                        v-if="isEditingUser"
                        :item="value"
                        :component="meta.user.formComponent"
                        :component-props="meta.user.formComponentProps"
                        @updated="itemUpdated"
                        @closed="isEditingUser = false"
                    />

                    <div class="flex flex-1 items-center justify-end">
                        <div class="flex items-center">
                            <Dropdown
                                v-if="
                                    (value.type === 'user' && value.editable) ||
                                    (value.type === 'guest' && meta.canCreateUsers)
                                "
                            >
                                <DropdownMenu>
                                    <DropdownItem
                                        v-if="value.type === 'user' && value.editable"
                                        :text="__('Edit')"
                                        icon="edit"
                                        @click="edit"
                                    />
                                    <DropdownItem
                                        v-else-if="value.type === 'guest' && meta.canCreateUsers"
                                        :text="__('Convert to User')"
                                        icon="add-user"
                                        @click="convertToUser"
                                    />
                                </DropdownMenu>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>