<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PendingWithdrawalOverview from './PendingWithdrawalOverview.vue';
import PendingWithdrawalTable from './pendingWithdrawalTable.vue';
import { ref } from 'vue';

const totalPendingAmount = ref(null);
const pendingWithdrawalCounts = ref(null);

const handleOverview = (data) => {
    totalPendingAmount.value = Number(data.totalPendingAmount);
    pendingWithdrawalCounts.value = data.pendingWithdrawalCounts;
}
</script>

<template>
    <AuthenticatedLayout title="pending_withdrawal">
        <div class="flex flex-col gap-5 items-center self-stretch w-full">
            <PendingWithdrawalOverview 
                :totalPendingAmount="totalPendingAmount"
                :pendingWithdrawalCounts="pendingWithdrawalCounts"
            />

            <PendingWithdrawalTable 
                @updatePendingWithdrawal="handleOverview"
            />

        </div>
    </AuthenticatedLayout>
</template>
