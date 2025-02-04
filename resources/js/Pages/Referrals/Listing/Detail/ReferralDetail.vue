<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from 'primevue/breadcrumb';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ReferralInfo from './ReferralInfo.vue';

const props = defineProps({
    referral: Object,
    refereeCount: Number,
    totalDownline: Number,
})

const home = ref({
    label: 'Referral Tree',
    route: route('referral.getReferralList')

});

const items = ref([
    { label: props.referral.name },
]);

</script>

<template>
    <AuthenticatedLayout :title="'Referral Tree'">
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
                <ReferralInfo 
                    :referral="referral"
                    :refereeCount="refereeCount"
                    :totalDownline="totalDownline"
                />

                
            </div>
        </div>
    </AuthenticatedLayout>
</template>