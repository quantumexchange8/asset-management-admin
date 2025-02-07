<script setup>
import Card from "primevue/card";
</script>

<template>
    <Card>
        <template #content>
            <div class="flex flex-col gap-1 items-start self-stretch">
                <span class="text-lg font-semibold">{{ $t('public.overview') }}</span>
                <span class="text-sm text-surface-500">{{ $t('public.overview_caption') }}</span>
            </div>
        </template>
    </Card>
</template>
