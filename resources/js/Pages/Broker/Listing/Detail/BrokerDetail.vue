<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import Breadcrumb from 'primevue/breadcrumb';
import { ref } from 'vue';
import BrokerInfo from './BrokerInfo.vue';

const props = defineProps({
    broker: Object,
    broker_image: Array,
    broker_qr_image: Array,
});

const home = ref({
    label: 'Broker Listing',
    route: route('broker.getBrokerList')

});
const items = ref([
    { label:  props.broker.name},
]);
</script>

<template>
    <AuthenticatedLayout :title="'Broker Listing'">
        <div class="flex flex-col gap-5">
            <Breadcrumb :home="home" :model="items">
                <template #item="{ item }">
                    <Link v-if="item.route" :href="item.route">
                    <span :class="[item.icon, 'text-color']" />
                    <span class="text-primary font-semibold hover:text-primary-600">{{ item.label }}</span>
                    </Link>
                    <span v-else class="text-surface-700 dark:text-surface-0">{{ item.label }} - {{ 'Details' }}</span>
                </template>
            </Breadcrumb>

            <div class="flex flex-col lg:flex-row items-center w-full gap-5 self-stretch">
                <BrokerInfo 
                    :broker="props.broker"
                    :broker_image="props.broker_image"
                    :broker_qr_image="props.broker_qr_image"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>