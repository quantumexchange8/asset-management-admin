<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TradeHistoryOverview from './TradeHistoryOverview.vue';
import TradeHistoryTable from './TradeHistoryTable.vue';
import {ref} from "vue";

const totalBonusAmount = ref(null);
const maxBonusAmount = ref(null);

const handleOverview = (data) => {
    totalBonusAmount.value = Number(data.totalBonusAmount);
    maxBonusAmount.value = Number(data.maxBonusAmount);
}
</script>

<template>
    <AuthenticatedLayout title="trade_history">
        <div class="flex flex-col items-start gap-5">
            <TradeHistoryOverview 
                :totalBonusAmount="totalBonusAmount"
                :maxBonusAmount="maxBonusAmount"
            />

            <TradeHistoryTable 
                @update-totals="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>