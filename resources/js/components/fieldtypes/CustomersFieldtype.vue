<template>
    <div class="relationship-input-items space-y-1 outline-none">
        <div class="item select-none item outline-none">
            <div class="item-inner">
                <div
                    v-if="value.invalid"
                    v-tooltip.top="__('An item with this ID could not be found')"
                    v-text="value.id" />

                <div v-else>
                    <a v-if="value.type === 'user' && value.editable" :href="value.edit_url" @click.prevent="edit" class="truncate v-popper--has-tooltip">
                        {{ value.name }}
                    </a>

                    <div v-else-if="value.type === 'guest'" class="truncate v-popper--has-tooltip">
                        {{ value.name }}
                        <div class="status-index-field select-none status-draft ml-1">Guest</div>
                    </div>

                    <div v-else v-text="value.name" />

                    <div v-if="value.email" class="text-xs mt-1 truncate text-gray-800 dark:text-dark-150" v-text="value.email"></div>
                </div>

                <inline-edit-form
                    v-if="isEditingUser"
                    :item="value"
                    :component="meta.user.formComponent"
                    :component-props="meta.user.formComponentProps"
                    @updated="itemUpdated"
                    @closed="isEditingUser = false"
                />

                <div class="flex items-center flex-1 justify-end">
                    <div class="flex items-center">
                        <dropdown-list v-if="(value.type === 'user' && value.editable) || (value.type === 'guest' && meta.canCreateUsers)" class="ml-2">
                            <dropdown-item v-if="value.type === 'user' && value.editable" :text="__('Edit')" @click="edit" />
                            <dropdown-item v-else-if="value.type === 'guest' && meta.canCreateUsers" :text="__('Convert to User')" @click="convertToUser" />
                        </dropdown-list>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import InlineEditForm from '@statamic/components/inputs/relationship/InlineEditForm.vue'
import { Fieldtype } from 'statamic';

export default {
    components: {
        InlineEditForm
    },

    mixins: [Fieldtype],

    inject: ['store'],

    data() {
        return {
            isEditingUser: false,
        }
    },

    methods: {
        edit() {
            if (! this.value.editable) return;
            if (this.value.invalid) return;

            if (this.value.reference) {
                const storeRefs = this.$pinia
                    ?._s.values()
                    .map((store) => store.reference);

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
            })
        },

        convertToUser() {
            axios.post(this.meta.convertGuestToUserUrl, {
                email: this.value.email,
                order_id: this.store.values.id,
            }).then(response => {
                this.$emit('update:value', response.data);
                this.$toast.success(__('Guest has been converted to a user.'));
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            });
        }
    }
}
</script>