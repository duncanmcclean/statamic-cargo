<template>
    <div class="mx-auto mt-4 max-w-lg">
        <div class="dark:shadow-dark rounded bg-white p-6 shadow dark:bg-dark-600 lg:px-20 lg:py-10">
            <header class="mb-16 text-center">
                <h1 class="mb-6">{{ __('Create Tax Class') }}</h1>
                <p class="text-gray" v-text="__('cargo::messages.tax_class_intro')" />
            </header>
            <div class="mb-10">
                <label class="mb-1 text-base font-bold" for="name">{{ __('Name') }}</label>
                <input type="text" v-model="name" class="input-text" autofocus tabindex="1" />
                <div class="text-2xs mt-2 flex items-center text-gray-600">
                    <span>{{ __('cargo::messages.tax_classes_name_instructions') }}</span>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <button tabindex="4" class="btn-primary btn-lg mx-auto" :disabled="!canSubmit" @click="submit">
                {{ __('Create Tax Class') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        route: {
            type: String,
        },
    },

    data() {
        return {
            name: null,
        };
    },

    computed: {
        canSubmit() {
            return Boolean(this.name);
        },
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
