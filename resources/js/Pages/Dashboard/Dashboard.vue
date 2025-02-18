<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {generalFormat} from "@/Composables/format.js";
import Card from "primevue/card";
import DashboardOverview from "@/Pages/Dashboard/DashboardOverview.vue";
import DashboardPayout from "@/Pages/Dashboard/DashboardPayout.vue";

const props = defineProps({
    totalActiveFund: String
});

const {formatAmount} = generalFormat();
</script>

<template>
    <AuthenticatedLayout title="dashboard">
        <div class="flex flex-col gap-5 items-center self-stretch">
            <div class="flex flex-col gap-1 w-full">
                <span class="text-5xl text-surface-950 dark:text-white font-semibold">${{ formatAmount(totalActiveFund) }}</span>
                <span class="text-sm text-surface-500">{{ $t('public.total_active_fund_from_broker') }}</span>
            </div>

            <div class="flex flex-col gap-5 items-center self-stretch w-full">
                <div class="flex flex-col gap-3 w-full">
                    <DashboardOverview />
                </div>
                <div class="flex flex-col gap-3 w-full">
                    <DashboardPayout />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
