<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-600 dark:text-red-[#DC2626]">
            {{ message }}
        </p>
    </div>
</template>
