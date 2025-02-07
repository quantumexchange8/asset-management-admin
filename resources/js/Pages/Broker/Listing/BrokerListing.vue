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
    <AuthenticatedLayout title="broker">
        <div class="flex flex-col gap-5 items-center self-stretch w-full">
            <div class="flex justify-end w-full">
                <AddBroker
                    :locales="locales"
                />
            </div>

            <BrokerTable
                :brokerCounts="brokerCounts"
                :locales="locales"
            />
        </div>
    </AuthenticatedLayout>
</template>
