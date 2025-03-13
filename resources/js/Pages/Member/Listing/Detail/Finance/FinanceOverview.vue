<script setup>
import { IconCashBanknoteOff, IconDatabaseDollar } from '@tabler/icons-vue';
import Card from 'primevue/card';
import Skeleton from 'primevue/skeleton';


</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 w-full gap-3 md:gap-5">
        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex flex-col items-start gap-3">
                        <div class="font-medium text-surface-500">
                            {{ $t("public.total_bonus") }}
                        </div>
                        <div class="text-xl font-semibold md:text-3xl">
                            <div v-if="isLoading">
                                <Skeleton width="5rem" height="2rem"></Skeleton>
                            </div>
                            <div v-else>
                                1000
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-blue-200 dark:bg-blue-700 w-14 h-14 text-blue-600 dark:text-blue-300">
                            <IconDatabaseDollar size="36" stroke-width="1.5" />
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
                            {{ $t("public.total_withdrawal") }}
                        </div>
                        <div class="text-xl font-semibold md:text-3xl">
                            <div v-if="isLoading">
                                <Skeleton width="5rem" height="2rem"></Skeleton>
                            </div>
                            <div v-else>
                               1000
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-red-200 dark:bg-red-700 w-14 h-14 text-red-600 dark:text-red-300">
                            <IconCashBanknoteOff size="36" stroke-width="1.5" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>