<template>
    <CreateForm
        :title="__('Create Tax Class')"
        :subtitle="__('cargo::messages.tax_class_intro')"
        :icon="icon"
        @submit="submit"
    >
        <ui-card-panel :heading="__('Tax Class Details')">
            <div class="space-y-8">
                <ui-field
                    :label="__('Name')"
                    :instructions="__('cargo::messages.tax_classes_name_instructions')"
                    :instructions-below="true"
                >
                    <ui-input v-model="name" autofocus tabindex="1" />
                </ui-field>
            </div>
        </ui-card-panel>
    </CreateForm>
</template>

<script>
import { CreateForm } from '@statamic/ui';

export default {
    components: {
        CreateForm,
    },

    props: {
        route: { type: String },
        icon: { type: String },
    },

    data() {
        return {
            name: null,
        };
    },

    methods: {
        submit() {
            this.$axios
                .post(this.route, { name: this.name })
                .then((response) => {
                    window.location = response.data.redirect;
                })
                .catch((error) => {
                    this.$toast.error(error.response.data.message);
                });
        },
    },

    mounted() {
        this.$keys.bindGlobal(['return'], (e) => {
            if (this.canSubmit) {
                this.submit();
            }
        });
    },
};
</script>
