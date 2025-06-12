<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-md border-gray-300 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-surface-950 dark:text-gray-300 dark:focus:border-primary dark:focus:ring-primary"
        v-model="model" ref="input" />
</template>
