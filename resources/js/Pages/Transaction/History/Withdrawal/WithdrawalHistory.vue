<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WithdrawalHistoryOverview from './WithdrawalHistoryOverview.vue';
import WithdrawalHistoryTable from './WithdrawalHistoryTable.vue';
import { ref } from 'vue';

const successAmount = ref(null);
const rejectAmount = ref(null);

const handleOverview = (data) => {
    successAmount.value = Number(data.successAmount);
    rejectAmount.value = Number(data.rejectAmount);
}
</script>

<template>
    <AuthenticatedLayout title="withdrawal_history">
        <div class="flex flex-col gap-5 items-center self-stretch">
            <WithdrawalHistoryOverview 
                :successAmount="successAmount"
                :rejectAmount="rejectAmount"
            />

            <WithdrawalHistoryTable 
                @updateWithdrawalHistory="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>
