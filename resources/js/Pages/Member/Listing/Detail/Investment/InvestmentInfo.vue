<script setup>
import EmptyData from '@/Components/EmptyData.vue';
import { generalFormat } from '@/Composables/format';
import { IconCircleLetterB } from '@tabler/icons-vue';
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';
import Tag from 'primevue/tag';
import { onMounted, ref } from 'vue';

const props = defineProps({
    user: Object,
    accountsCount: Number,
});

const accounts = ref();
const accountsCount = ref();
const isLoading = ref(false);
const {formatAmount} = generalFormat();

const getBrokerAccounts = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/member/detail/${props.user.id_number}/get_broker_accounts`);

        accounts.value = response.data.brokerAccounts;
      
    } catch(error) {
        console.error('Error fetching accounts:', error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    getBrokerAccounts();
});

const formattedBalance = (data) => {
    const amount = formatAmount(data.broker_capital);
    const parts = amount.split(".");

    return parts.length > 1
        ? `${parts[0]}.<span class='text-lg text-surface-400'>${parts[1]}</span>`
        : amount;
};

const getSeverity = (status) => {
    switch (status) {
        case 'linked':
            return 'success';

        case 'unlink':
            return 'danger';

        case 'rejected':
            return 'danger';

        case 'pending':
            return 'warn';
    }
};
</script>

<template>
    <div
        v-if="props.accountsCount === 0"
        class="flex flex-col justify-center items-center w-full"
    >
        <EmptyData
            :title="$t('public.no_connection')"
        />
    </div>

    <div v-else class="flex flex-col items-center gap-5">
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 w-full gap-3 md:gap-5">
            <Card 
                v-for="index in props.accountsCount"
                :key="index"
            >
                <template #content>
                    <div class="flex gap-3 items-center justify-between">
                        <div class="flex items-center gap-2 self-stretch">
                            <div
                                class="w-10 h-10 rounded-full grow-0 shrink-0 flex items-center justify-center border border-surface-200 dark:border-surface-800 text-surface-300 dark:text-surface-600"
                            >
                                <IconCircleLetterB size="28" stroke-width="1.5"/>
                            </div>
                            <div class="self-stretch flex items-center gap-2 truncate text-sm text-surface-600 dark:text-surface-500">
                                <Skeleton width="5rem" height="1.25rem" class="my-1"></Skeleton>
                                <Skeleton width="3rem" height="1.25rem"></Skeleton>
                            </div>
                        </div>
                        <div class="font-semibold text-lg dark:text-white">
                            <Skeleton width="4rem" height="1.25rem"></Skeleton>
                        </div>
                    </div>
                    <div class="text-3xl font-medium dark:text-white">
                        <Skeleton width="7rem" height="2rem" class="mt-5"></Skeleton>
                    </div>
                </template>
            </Card>
        </div>
       
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 w-full gap-3 md:gap-5">
            <Card v-for="account in accounts">
                <template #content>
                    <div class="flex gap-3 items-center justify-between">
                        <div class="flex items-center gap-2 self-stretch">
                            <img
                                v-if="account.broker.media"
                                class="object-cover w-5 h-5 rounded-full border border-surface-200 dark:border-surface-800"
                                :src="account.broker.media[0].original_url"
                                alt="broker_image"
                            />
                            <div
                                v-else
                                 class="w-10 h-10 rounded-full grow-0 shrink-0 flex items-center justify-center border border-surface-200 dark:border-surface-800 text-surface-300 dark:text-surface-600"
                            >
                                <IconCircleLetterB size="28" stroke-width="1.5"/>
                            </div>
                            <div class="self-stretch flex items-center gap-2 truncate text-sm text-surface-600 dark:text-surface-500">
                                {{ account.broker.name }}
                                <Tag
                                    :severity="getSeverity(account.status)"
                                    :value="$t(`public.${account.status}`)"
                                />
                            </div>
                        </div>
                        <div class="font-semibold text-lg dark:text-white">
                            #{{ account.broker_login }}
                        </div>
                    </div>
                    <div class="text-3xl font-medium dark:text-white">
                        <span v-html="formattedBalance(account)"></span>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>