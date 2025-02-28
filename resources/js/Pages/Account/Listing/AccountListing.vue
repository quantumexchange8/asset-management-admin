<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AccountTable from './AccountTable.vue';
import AccountOverview from './AccountOverview.vue';
import { ref } from 'vue';

const totalBrokerCapital = ref(null);
const accountCounts = ref(null);

const handleOverview = (data) => {
    totalBrokerCapital.value = Number(data.totalBrokerCapital);
    accountCounts.value = data.accountCounts;
}
</script>

<template>
    <AuthenticatedLayout title="account_listing">
        <div class="flex flex-col gap-5 items-center self-stretch w-full">

            <AccountOverview 
                :totalBrokerCapital="totalBrokerCapital"
                :accountCounts="accountCounts"
            />

            <AccountTable 
                @update-totals="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>