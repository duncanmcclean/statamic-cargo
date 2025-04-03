<template>
    <component v-if="icon" :is="icon" />
</template>

<script setup>
import { defineAsyncComponent, shallowRef, watch } from 'vue';

const props = defineProps({
    name: String,
    default: String,
    directory: String,
});

const icon = shallowRef(null);

const evaluateIcon = () => {
    if (props.name.startsWith('<svg')) {
        return defineAsyncComponent(() => {
          return new Promise((resolve) => resolve({ template: props.name }));
        });
    }

    return defineAsyncComponent(() => {
        return import(`./../../svg/${props.name}.svg`);
    });
};

watch(
    () => props.name,
    () => {
      icon.value = evaluateIcon();
    },
    { immediate: true },
);
</script>