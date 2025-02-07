<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BrokerTable from './BrokerTable.vue';
import AddBroker from './AddBroker.vue';

const props = defineProps({
    brokerCounts: Number,
    locales: Array,
});
</script>

<template>
    <AuthenticatedLayout title="broker_list">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->
            <div class="flex justify-end w-full">
                <AddBroker 
                    :locales="props.locales"
                />
            </div>
          
            <BrokerTable 
                :brokerCounts="props.brokerCounts"
                :locales="props.locales"
            />
        </div>
    </AuthenticatedLayout>
</template>