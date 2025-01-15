<script setup>
import {onMounted, ref, watchEffect } from 'vue';
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';
import WalletAdjustment from './WalletAdjustment.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    required: true,
});

const wallets = ref(); //wallets.value where value pass back in here
const isLoading = ref(false);

const getWalletData = async () => {
    isLoading.value = true;
    try{
        const response = await axios.get(`/member/detail/${props.user.id_number}/getWalletData`);
        wallets.value = response.data;
    } catch(error){
        console.error('Error fetching wallet data:', error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    getWalletData();
});

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getWalletData();
    }
});

</script>

<template>
    <div class="flex flex-col xl:flex-row items-start gap-5 self-stretch">
        <div class="flex flex-col gap-5 w-full xl:max-w-[640px]">
            <div
                v-if="isLoading"
                class="grid grid-cols-2 gap-3 w-full"
            >
                <Card
                    v-for="index in user.wallets_count"
                >
                    <template #content>
                        <div class="flex flex-col gap-3 items-start self-stretch">
                            <div class="flex justify-between items-center self-stretch">
                                <Skeleton width="5rem" class="my-[11px]"></Skeleton>
                            </div>

                            <div class="w-full bg-surface-100 dark:bg-surface-800 p-3 flex flex-col gap-3">
                                <span class="text-surface-700 dark:text-surface-100 text-xs font-medium">Balance</span>
                                <div class="md:text-lg text-primary-500 font-semibold">
                                    <Skeleton width="5rem" height="1.25rem" class="my-1"></Skeleton>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div v-else class="grid grid-cols-2 gap-5 w-full">
                <Card
                    v-for="wallet in wallets"
                >
                    <template #content>
                        
                        <div class="flex flex-col gap-3 items-start self-stretch">
                            <div class="flex justify-between items-center self-stretch">
                                <span class="text-xs md:text-sm dark:text-surface-400"> {{ wallet.type.replace('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase()) }}</span>
                                <WalletAdjustment
                                    :user="user"
                                    :wallet="wallet"
                                />
                            </div>

                            <div class="w-full bg-surface-100 dark:bg-surface-800 p-3 flex flex-col gap-3">
                                <span class="text-surface-700 dark:text-surface-100 text-xs font-medium">Balance</span>
                                <div class="md:text-lg text-primary-500 font-semibold">
                                    <span>{{ wallet.currency_symbol }}{{ wallet.balance }}</span>
                                    <!-- <span v-else>{{ wallet.balance }} {{wallet.currency_symbol }}</span> -->
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>