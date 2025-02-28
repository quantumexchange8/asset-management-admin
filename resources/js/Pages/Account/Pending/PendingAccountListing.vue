<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import { ref } from 'vue';
import PendingAccountTable from './PendingAccountTable.vue';
import PendingAccountOverview from './PendingAccountOverview.vue';

const pendingAccountCounts = ref(null);
const totalPendingCapital = ref(null);

const handleOverview = (data) => {
    pendingAccountCounts.value = data.pendingAccountCounts;
    totalPendingCapital.value = Number(data.totalPendingCapital);
}
</script>

<template>
    <AuthenticatedLayout title="pending_account">
        <div class="flex flex-col gap-5 items-center self-stretch w-full">

            <PendingAccountOverview 
                :pendingAccountCounts="pendingAccountCounts"
                :totalPendingCapital="totalPendingCapital"
            />

            <PendingAccountTable 
                @update-totals="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>