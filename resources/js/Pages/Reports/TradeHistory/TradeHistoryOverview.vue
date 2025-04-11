<script setup>
import Skeleton from "primevue/skeleton";
import {
    IconDatabaseDollar,
    IconMedal
} from "@tabler/icons-vue";
import Card from "primevue/card";
import {ref} from "vue";
import {generalFormat} from "@/Composables/format.js";
import {max} from "rxjs";

const props = defineProps({
    totalBonusAmount: Number,
    maxBonusAmount: Number,
})

const {formatAmount} = generalFormat();
</script>

<template>
     <div class="grid grid-cols-1 md:grid-cols-2 w-full gap-3 md:gap-5">
        <Card>
            <template #content>
                <div class="flex justify-between items-center">
                    <div class="flex items-end gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                {{ $t('public.total_bonus') }}
                            </div>
                            <div class="text-3xl font-semibold">
                                <div v-if="totalBonusAmount === null">
                                    <Skeleton width="5rem" class="my-0.5" height="2rem"></Skeleton>
                                </div>

                                <div v-else>
                                    {{ formatAmount(totalBonusAmount, 4) }}
                                </div>
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
                    <div class="flex items-end gap-1">
                        <div class="flex flex-col items-start gap-2">
                            <div class="text-surface-500 text-sm">
                                {{ $t('public.highest_bonus') }}
                            </div>
                            <div class="text-3xl font-semibold">
                                <div v-if="maxBonusAmount === null">
                                    <Skeleton width="5rem" class="my-0.5" height="2rem"></Skeleton>
                                </div>

                                <div v-else>
                                    {{ formatAmount(maxBonusAmount, 4) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 w-[72px] h-[72px]">
                        <div class="flex items-center justify-center rounded-full bg-primary-200 dark:bg-primary-700 w-14 h-14 text-primary-600 dark:text-primary-300">
                            <IconMedal size="36" stroke-width="1.5" />
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>