<script setup>
import Card from "primevue/card";
import SelectButton from "primevue/selectbutton";
import { ref } from "vue";

const value = ref('One-Way');
const options = ref(['One-Way', 'Return', 'Hello']);
</script>

<template>
    <Card>
        <template #content>
            <div class="flex flex-col gap-1 items-start self-stretch">
                <span class="text-lg font-semibold">{{ $t('public.overview') }}</span>
                <span class="text-sm text-surface-500">{{ $t('public.overview_caption') }}</span>
                <SelectButton v-model="value" :options="options" />
            </div>
        </template>
    </Card>
</template>
