<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PendingDepositTable from './PendingDepositTable.vue';
import PendingDepositOverview from './PendingDepositOverview.vue';
import { ref } from 'vue';

const totalPendingAmount = ref(null);
const pendingDepositCounts = ref(null);

const handleOverview = (data) => {
    totalPendingAmount.value = Number(data.totalPendingAmount);
    pendingDepositCounts.value = data.pendingDepositCounts;
}
</script>

<template>
    <AuthenticatedLayout title="pending_deposit">
        <div class="space-y-4">
            <PendingDepositOverview 
                :totalPendingAmount="totalPendingAmount"
                :pendingDepositCounts="pendingDepositCounts"
            />

            <PendingDepositTable 
                @updatePendingDeposit="handleOverview"
            />

        </div>
    </AuthenticatedLayout>
</template>
