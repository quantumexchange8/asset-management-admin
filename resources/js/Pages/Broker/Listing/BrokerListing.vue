<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BrokerTable from './BrokerTable.vue';

const props = defineProps({
    brokerCounts: Number,
});
</script>

<template>
    <AuthenticatedLayout :title="'Broker List'">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->
            <BrokerTable :brokerCounts="brokerCounts"/>
        </div>
    </AuthenticatedLayout>
</template>