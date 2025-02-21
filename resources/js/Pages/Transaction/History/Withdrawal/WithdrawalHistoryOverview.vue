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
                                    $ {{ formatAmount(props.successAmount ?? 0) }}
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
                                    $ {{ formatAmount(props.rejectAmount ?? 0) }}
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
                    <span class="text-surface-700 dark:text-surface-300 font-semibold text-sm">{{  }}</span>
                   
                </div>
            </template>
        </Card>
    </div>
</template>