<script setup>
import Card from "primevue/card";
import SelectButton from "primevue/selectbutton";
import DatePicker from "primevue/datepicker";
import { computed, ref, watch } from "vue";
import DashboardPayoutsChart from "./partial/DashboardPayoutsChart.vue";

const date = ref(new Date());

const selectedYear = computed(() => {
    return date.value ? date.value.getFullYear() : "";
});
</script>

<template>
    <Card>
        <template #content>
            <div class="flex flex-col gap-5">
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <span class="text-lg font-semibold">{{ $t('public.payouts') }}</span>
                        <span class="text-sm text-surface-500">{{ $t('public.payouts_caption') }}</span>
                    </div>
        
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <!-- Date Picker -->
                        <DatePicker
                            v-model="date"
                            view="year"
                            dateFormat="yy"
                            inputClass="!w-16"
                            panelClass="!w-48"
                        />
                    </div>
                </div>

                <DashboardPayoutsChart 
                    :selectedYear="selectedYear"
                    
                />
            </div>
        </template>
    </Card>
</template>
