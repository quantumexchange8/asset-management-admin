<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PendingConnectionTable from './PendingConnectionTable.vue';
import PendingConnectionOverview from './PendingConnectionOverview.vue';
import {ref} from "vue";

const totalPendingFund = ref(null);
const totalPendingConnections = ref(null);

const handleOverview = (data) => {
    totalPendingFund.value = Number(data.totalPendingFund);
    totalPendingConnections.value = data.totalPendingConnections;
}
</script>

<template>
    <AuthenticatedLayout title="pending_connection">
        <div class="flex flex-col items-center gap-5">
            <PendingConnectionOverview 
                :totalPendingFund="totalPendingFund"
                :totalPendingConnections="totalPendingConnections"
            />
            
            <PendingConnectionTable 
                @update-totals="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>