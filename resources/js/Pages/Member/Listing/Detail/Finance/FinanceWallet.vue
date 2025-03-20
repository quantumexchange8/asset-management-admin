<script setup>
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';
import WalletAdjustment from './WalletAdjustment.vue';
import { onMounted, ref } from 'vue';
import { generalFormat } from '@/Composables/format';

const props = defineProps({
    user: Object,
    required: true,
});

const wallets = ref(); //wallets.value where value pass back in here
const isLoading = ref(false);
const {formatAmount} = generalFormat();

const getWalletData = async () => {
    isLoading.value = true;
    try{
        const response = await axios.get(`/member/detail/${props.user.id_number}/getWalletData`);
        wallets.value = response.data.filter(wallet => wallet.type !== 'cash_wallet');
    } catch(error){
        console.error('Error fetching wallet data:', error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    getWalletData();
});

const refreshWallet = () => {
    getWalletData();
};

</script>

<template>
    <div
        v-if="isLoading"
        class="flex flex-col gap-5"
    >
        <Card
            v-for="index in user.wallets_count"
        >
            <template #content>
                <div class="flex flex-col gap-3 items-start self-stretch">
                    <div class="flex justify-between items-center self-stretch">
                        <Skeleton width="5rem" class="my-[11px]"></Skeleton>
                    </div>

                    <div class="w-full bg-surface-100 dark:bg-surface-800 p-3 flex flex-col gap-3 rounded-[12px]">
                        <span class="text-surface-700 dark:text-surface-100 text-xs font-medium">Balance</span>
                        <div class="md:text-lg text-primary-500 font-semibold">
                            <Skeleton width="5rem" height="1.25rem" class="my-1"></Skeleton>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>

    <div v-else class="flex flex-col">
        <Card v-for="wallet in wallets" :key="wallet.id">
            <template #content>
                <div class="flex flex-col gap-3 items-start self-stretch">
                    <div class="flex justify-between items-center self-stretch">
                        <span class="text-xs md:text-sm dark:text-surface-400">{{ $t(`public.${wallet.type}`) }}</span>
                        <WalletAdjustment
                            :user="user"
                            :wallet="wallet"
                            @walletAdjusted="refreshWallet"
                        />
                    </div>
                    <div class="w-full bg-surface-100 dark:bg-surface-800 p-3 flex flex-col gap-3 rounded-[12px]">
                        <span class="text-surface-700 dark:text-surface-100 text-xs font-medium">{{ $t('public.balance') }}</span>
                        <div class="md:text-lg text-primary-500 font-semibold">
                            <span>{{ formatAmount(wallet.balance) }}</span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>