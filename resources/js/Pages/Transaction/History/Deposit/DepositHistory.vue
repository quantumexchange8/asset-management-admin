<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DepositHistoryTable from './DepositHistoryTable.vue';
import DepositHistoryOverview from './DepositHistoryOverview.vue';
import { ref } from 'vue';

const successAmount = ref(null);
const rejectAmount = ref(null);

const handleOverview = (data) => {
    successAmount.value = Number(data.successAmount);
    rejectAmount.value = Number(data.rejectAmount);
}
</script>

<template>
    <AuthenticatedLayout title="deposit_history">
        <div class="flex flex-col gap-5 items-center self-stretch">

            <DepositHistoryOverview 
                :successAmount="successAmount"
                :rejectAmount="rejectAmount"
            />

            <DepositHistoryTable 
                @updateDepositHistory="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>
