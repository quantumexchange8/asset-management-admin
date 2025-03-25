<script setup>
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';
import Avatar from 'primevue/avatar';
import { generalFormat } from '@/Composables/format';
import { IconCurrencyDollar, IconCurrencyDollarOff } from '@tabler/icons-vue';
import { onMounted, ref } from 'vue';

const props = defineProps({
    successAmount: Number,
    rejectAmount: Number,
});

const isLoading = ref(false);
const topUsers = ref([]);
const totalSuccessAmount = ref();
const { formatAmount } = generalFormat();
const {formatNameLabel} = generalFormat();

const getHighestDeposit = async () => {
    isLoading.value = true;

    try {
        const response = await axios.get('/transaction/history/get_highest_deposit');
        topUsers.value = response.data.topUsers;
        totalSuccessAmount.value = response.data.totalSuccessAmount;
    } catch(error) {
        console.error('Error fetching highest deposit:', error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    getHighestDeposit();
});

const getProfilePhoto = (user) => {
    if (!user || !user.media) return null;
    const profilePhoto = user.media.find(m => m.collection_name === "profile_photo");
    return profilePhoto ? profilePhoto.original_url : null;
};


function calculatePercentage(fund) {
    if (!totalSuccessAmount.value || !fund) {
        return 0;
    }
    return ((fund / totalSuccessAmount.value) * 100).toFixed(2);
}
</script>

<template>
    <div class="flex flex-col lg:flex-row gap-3 md:gap-5 w-full">
        <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-3 md:gap-5 lg:min-w-80 xl:min-w-96">
            <Card>
                <template #content>
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col items-start gap-3">
                            <div class="font-medium text-surface-500">
                                {{ $t("public.success_amount") }}
                            </div>
                            <div class="text-xl font-semibold md:text-3xl">
                                <div v-if="isLoading">
                                    <Skeleton width="5rem" height="2rem"></Skeleton>
                                </div>

                                <div v-else>
                                    {{ formatAmount(props.successAmount ?? 0) }}
                                </div>

                            </div>
                        </div>
                        <div class="flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40 w-[72px] h-[72px]">
                            <div class="flex items-center justify-center rounded-full bg-green-200 dark:bg-green-700 w-14 h-14 text-green-600 dark:text-green-300">
                                <IconCurrencyDollar size="36" stroke-width="1.5" />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col items-start gap-3">
                            <div class="font-medium text-surface-500">
                                {{ $t("public.reject_amount") }}
                            </div>
                            <div class="text-xl font-semibold md:text-3xl">
                                <div v-if="isLoading">
                                    <Skeleton width="5rem" height="2rem"></Skeleton>
                                </div>
                                
                                <div v-else>
                                    {{ formatAmount(props.rejectAmount ?? 0) }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40 w-[72px] h-[72px]">
                            <div class="flex items-center justify-center rounded-full bg-red-200 dark:bg-red-700 w-14 h-14 text-red-600 dark:text-red-300">
                                <IconCurrencyDollarOff size="36" stroke-width="1.5" />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <Card class="w-full">
            <template #content>
                <div class="flex flex-col items-start gap-3 md:gap-5">
                    <span class="text-surface-700 dark:text-surface-300 font-semibold text-sm">{{ $t('public.top_three_user_deposit') }}</span>
                    <div class="flex flex-col gap-3 items-center self-stretch w-full">
                        <div v-for="index in 3" :key="index" class="flex items-center py-2 gap-3 md:gap-4 w-full">
                            <div class="w-full flex items-center min-w-[140px] md:min-w-[180px] md:max-w-[240px] gap-3 md:gap-4">
                                <div class="w-8 h-8 rounded-full overflow-hidden grow-0 shrink-0">
                                    <Avatar
                                        v-if="getProfilePhoto(topUsers[index - 1]?.user)"
                                        :image="getProfilePhoto(topUsers[index - 1]?.user)"
                                        shape="circle"
                                    />
                                    
                                    <Avatar
                                        v-else
                                        :label="formatNameLabel(topUsers[index - 1]?.user.name)"
                                        shape="circle"
                                    />
                                </div>
                                <span class="truncate w-full max-w-[180px] md:max-w-[200px] text-surface-950 dark:text-white text-sm font-medium md:text-base">
                                    {{ topUsers[index - 1]?.user.name || '------' }}
                                </span>
                            </div>
                            <div class="w-full max-w-[52px] xs:max-w-sm sm:max-w-md md:max-w-full h-1 bg-surface-100 dark:bg-surface-700 rounded-full relative">
                                <div
                                    class="absolute top-0 left-0 h-full rounded-full bg-primary-500 transition-all duration-700 ease-in-out"
                                    :style="{ width: `${calculatePercentage(topUsers[index - 1]?.total_deposit)}%` }"
                                />
                            </div>
                            <span class="truncate text-surface-950 dark:text-white text-right text-sm font-medium md:text-base w-full max-w-[88px] md:max-w-[110px]">
                                $ {{ topUsers[index - 1]?.total_deposit ? formatAmount(topUsers[index - 1].total_deposit) : formatAmount(0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>