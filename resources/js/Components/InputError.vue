<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
     <div class="text-sm text-red-600 dark:text-red-[#DC2626]">
        <!-- If there's HTML content, render it using v-html -->
        <div v-if="message" v-html="message"></div>
        
        <!-- Fallback plain text (when no HTML is used) -->
        <p v-else class="text-sm text-red-600 dark:text-red-[#DC2626]">
            {{ message }}
        </p>
    </div>
</template>
