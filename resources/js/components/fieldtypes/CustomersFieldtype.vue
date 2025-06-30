<template>
    <div class="relationship-input @container h-full w-full">
        <div>
            <div
                class="shadow-ui-sm related-item relative z-2 mb-1.5 flex h-full w-full items-center gap-2 rounded-lg border border-gray-200 bg-white px-1.5 py-1.5 text-base last:mb-0 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black"
            >
                <div class="flex flex-1 items-center p-1">
                    <Heading v-if="value.invalid">
                        <Tooltip :text="__('An item with this ID could not be found')" :delay="1000">
                            {{ value.id }}
                            <Badge pill color="red" :text="__('Invalid')" />
                        </Tooltip>
                    </Heading>

                    <div v-else>
                        <Heading v-if="value.type === 'user' && value.editable">
                            <a :href="value.edit_url" @click.prevent="edit" v-text="value.name" />
                        </Heading>

                        <Heading v-else-if="value.type === 'guest'" class="truncate">
                            {{ value.name }}
                            <Badge pill size="sm" :text="__('Guest')" />
                        </Heading>

                        <Heading v-else :text="value.name" />
                        <Description v-if="value.email" :text="value.email" />
                    </div>

                    <inline-edit-form
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

<script>
import axios from 'axios';
import InlineEditForm from '@statamic/components/inputs/relationship/InlineEditForm.vue';
import { Fieldtype } from 'statamic';
import { Dropdown, DropdownMenu, DropdownItem, Heading, Description, Badge, Tooltip } from '@statamic/ui';

export default {
    components: {
        InlineEditForm,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        Heading,
        Description,
        Badge,
        Tooltip,
    },

    mixins: [Fieldtype],

    inject: ['store'],

    data() {
        return {
            isEditingUser: false,
        };
    },

    methods: {
        edit() {
            if (!this.value.editable) return;
            if (this.value.invalid) return;

            if (this.value.reference) {
                const storeRefs = this.$pinia?._s.values().map((store) => store.reference);

                if (Array.from(storeRefs).includes(this.value.reference)) {
                    this.$toast.error(__("You're already editing this item."));
                    return;
                }
            }

            this.isEditingUser = true;
        },

        itemUpdated(responseData) {
            this.$emit('update:value', {
                ...this.value,
                // in case we need to merge anything in here
            });
        },

        convertToUser() {
            axios
                .post(this.meta.convertGuestToUserUrl, {
                    email: this.value.email,
                    order_id: this.store.values.id,
                })
                .then((response) => {
                    this.$emit('update:value', response.data);
                    this.$toast.success(__('Guest has been converted to a user.'));
                })
                .catch((error) => {
                    this.$toast.error(error.response.data.message);
                });
        },
    },
};
</script>
