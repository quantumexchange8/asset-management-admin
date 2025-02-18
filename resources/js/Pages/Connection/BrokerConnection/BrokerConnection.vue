<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ConnectionOverview from "@/Pages/Connection/BrokerConnection/ConnectionOverview.vue";
import ConnectionTable from "@/Pages/Connection/BrokerConnection/ConnectionTable.vue";
import ImportConnection from "@/Pages/Connection/BrokerConnection/ImportConnection.vue";
import {ref} from "vue";

const props = defineProps({
    connectionCounts: Number,
});

const totalActiveFund = ref(null);
const totalConnections = ref(null);

const handleOverview = (data) => {
    totalActiveFund.value = Number(data.totalActiveFund);
    totalConnections.value = data.totalConnections;
}
</script>

<template>
    <AuthenticatedLayout title="broker_connection">
        <div class="flex flex-col items-center gap-5">
            <ConnectionOverview
                :totalActiveFund="totalActiveFund"
                :totalConnections="totalConnections"
            />

            <div class="flex justify-end w-full">
                <ImportConnection />
            </div>

            <ConnectionTable
                :connectionCounts="connectionCounts"
                @update-totals="handleOverview"
            />
        </div>
    </AuthenticatedLayout>
</template>
