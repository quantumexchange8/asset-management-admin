<script setup>
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';
import Timeline from 'primevue/timeline';
import ScrollPanel from 'primevue/scrollpanel';
import Tag from 'primevue/tag';
import { generalFormat } from '@/Composables/format';
import dayjs from 'dayjs';
import { onMounted, ref } from 'vue';
import { IconBellDollar, IconClockDollar } from '@tabler/icons-vue';
import EmptyData from '@/Components/EmptyData.vue';

const props = defineProps({
    totalPendingAmount: Number,
    pendingDepositCounts: Number,
});

const isLoading = ref(false);
const recentApprovals = ref([]);
const { formatAmount } = generalFormat();

const getRecentApproval = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/transaction/pending/get_deposit_recent_approval');
        recentApprovals.value = response.data.recentApprovals;
    } catch (error) {
        console.error('Error fetching recent approvals:', error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    getRecentApproval();
});

//status severity
const getSeverity = (status) => {
    switch (status) {

        case 'success':
            return 'success';

        case 'rejected':
            return 'danger';
    }
};

</script>

<template>
    <div class="flex flex-col lg:flex-row gap-3 md:gap-5 w-full">
        <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-3 md:gap-5 lg:min-w-80 xl:min-w-96">
            <Card>
                <template #content>
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col items-start gap-3">
                            <div class="font-medium text-surface-500">
                                {{ $t("public.total_pending_deposit") }}
                            </div>
                            <div class="text-xl lowercase">
                                <div v-if="isLoading">
                                    <Skeleton width="5rem" height="2rem"></Skeleton>
                                </div>
    
                                <div v-else>
                                    {{ props.pendingDepositCounts }} {{ $t('public.deposit') }}
                                </div>
    
                            </div>
                        </div>
                        <div class="flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 w-[72px] h-[72px]">
                            <div class="flex items-center justify-center rounded-full bg-primary-200 dark:bg-primary-700 w-14 h-14 text-primary-600 dark:text-primary-300">
                                <IconBellDollar size="36" stroke-width="1.5" />
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
                                {{ $t("public.total_pending_amount") }}
                            </div>

                            <div v-if="isLoading">
                                <Skeleton width="5rem" height="2rem"></Skeleton>
                            </div>

                            <div v-else class="text-xl">
                                ${{ formatAmount(props.totalPendingAmount ?? 0) }}
                            </div>
                        </div>

                        <div class="flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40 w-[72px] h-[72px]">
                            <div class="flex items-center justify-center rounded-full bg-blue-200 dark:bg-blue-700 w-14 h-14 text-blue-600 dark:text-blue-300">
                                <IconClockDollar size="36" stroke-width="1.5" />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
        <Card class="w-full">
            <template #content>
                <div class="flex flex-col items-start gap-3 md:gap-5">
                    <span class="text-surface-700 dark:text-surface-300 font-semibold text-sm">{{ $t('public.recent_approval') }}</span>
                    <ScrollPanel style="width: 100%; height: 150px">
                        <div v-if="recentApprovals.length <= 0">
                            <EmptyData 
                                :title="$t('public.no_data')"
                                :message="$t('public.no_recent_transaction')"
                            />
                        </div>
                        <Timeline :value="recentApprovals" v-else>
                            <template #opposite="slotProps">
                                <div class="flex flex-col"> 
                                    <small class="text-surface-500 dark:text-surface-400">{{ dayjs(slotProps.item.approval_at).add(8, 'hour').format('YYYY/MM/DD') }}</small>
                                    <small class="text-xs text-surface-500 dark:text-surface-400">{{ dayjs(slotProps.item.approval_at).add(8, 'hour').format('hh:mm:ss A') }}</small>
                                </div>
                            </template>
                            <template #content="slotProps">
                                <div class="flex gap-2 items-center">
                                    <div class="flex flex-col"> 
                                        <span class="text-surface-950 dark:text-white">{{ slotProps.item.transaction_number }}</span>
                                        <span class="text-xs text-surface-500">{{ $t('public.approved_by') }}: {{ slotProps.item.approval_by.name }}</span>
                                    </div>
                                    <Tag
                                        :value="$t(`public.${slotProps.item.status}`)"
                                        :severity="getSeverity(slotProps.item.status)"
                                    />
                                </div>
                            </template>
                        </Timeline>
                    </ScrollPanel>
                </div>
            </template>
        </Card>
    </div>
</template>