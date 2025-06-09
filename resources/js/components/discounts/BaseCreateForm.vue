<template>
    <discount-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :initial-title="title"
        :initial-fieldset="fieldset"
        :initial-values="values"
        :initial-meta="meta"
        :create-another-url="createAnotherUrl"
        :initial-listing-url="listingUrl"
        @saved="saved"
    ></discount-publish-form>
</template>

<script>
export default {
    props: ['title', 'actions', 'fieldset', 'values', 'meta', 'createAnotherUrl', 'listingUrl'],

    methods: {
        saved(response) {
            window.location = response.data.data.edit_url + '?created=true';
        },
    },
};
</script>
