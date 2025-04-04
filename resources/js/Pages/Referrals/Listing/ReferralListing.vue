<script setup>
import { ref } from "vue";
import EmptyData from "@/Components/EmptyData.vue";
import { IconCircleLetterB } from "@tabler/icons-vue";
import { generalFormat } from "@/Composables/format.js";
import Skeleton from "primevue/skeleton";
import Tag from "primevue/tag";
import Button from "primevue/button";
import debounce from "lodash/debounce.js";

const props = defineProps({
    parentCount: Number,
})

const parents = ref([]);
const selectedReferral = ref(null);
const isLoading = ref(false);
const { formatAmount } = generalFormat();
const emit = defineEmits(['update:referral']);

const getReferral = async () => {
    isLoading.value = true;

    try {
        let url = '/referral/get_referral';

        const response = await axios.get(url);

        parents.value = response.data.parents;
        selectedReferral.value = parents.value[0]
        if (selectedReferral.value) {
            emit('update:referral', selectedReferral.value)
        }
    } catch (error) {
        console.error('Error getting referral:', error);
    } finally {
        isLoading.value = false;
    }
}

getReferral();

const selectReferral = (data) => {
    selectedReferral.value = data;
    emit('update:referral', data)
}
</script>

<template>
    <div class="w-full">
        <div v-if="isLoading" class="flex gap-5 items-center overflow-x-auto w-full">
            <div
                v-for="parent in parentCount"
                :key="parent.id"
                class="rounded flex flex-col gap-2 min-w-80 p-1 md:min-w-96 shadow-card border-l-4 select-none w-full md:basis-1/3 xl:basis-1/4 bg-white dark:bg-surface-800 border-primary border-t border-t-surface-200 dark:border-t-surface-700 border-b border-b-surface-200 dark:border-b-surface-700 border-r border-r-surface-200 dark:border-r-surface-700 transition-all duration-200">
                <div class="pt-3 pb-2 px-3 rounded-t flex flex-col w-full self-stretch">
                    <Skeleton width="5rem" class="my-0.5" height="1rem"></Skeleton>
                    <Skeleton width="7rem" class="my-0.5" height="1rem"></Skeleton>
                    <Skeleton width="9rem" class="my-0.5" height="1rem"></Skeleton>
                </div>

                <div class="pb-2 px-3 rounded-b grid grid-cols-2 gap-3 w-full self-stretch text-sm">
                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                        <Skeleton width="3rem" class="my-0.5" height="1rem"></Skeleton>
                        <span class="text-xs uppercase">{{ $t('public.directs') }}</span>
                    </div>
                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                        <Skeleton width="3rem" class="my-0.5" height="1rem"></Skeleton>
                        <span class="text-xs uppercase">{{ $t('public.networks') }}</span>
                    </div>
                </div>

                <div class="pb-2 px-3">
                    <Skeleton class="my-0.5" height="2rem"></Skeleton>
                </div>
            </div>
        </div>

        <div v-else class="flex gap-5 items-center overflow-x-auto w-full">
            <!-- Loop through each parent -->
            <div
                v-for="parent in parents"
                :key="parent.id"
                class="rounded flex flex-col gap-2 min-w-80 p-1 md:min-w-96 shadow-card border-l-4 select-none w-full md:basis-1/3 xl:basis-1/4 bg-white dark:bg-surface-800 border-primary border-t border-t-surface-200 dark:border-t-surface-700 border-b border-b-surface-200 dark:border-b-surface-700 border-r border-r-surface-200 dark:border-r-surface-700 transition-all duration-200">
                <div class="pt-3 pb-2 px-3 rounded-t flex flex-col items-center w-full self-stretch">
                    <div class="w-full text-sm font-semibold text-surface-950 dark:text-white truncate">
                        {{ parent.username }}
                    </div>
                    <div class="w-full text-sm text-surface-400 truncate">
                        {{ $t('public.fund') }}:
                        <span class="font-semibold text-primary">{{ formatAmount(parent.capital_fund_sum) }}</span>
                    </div>
                    <div class="w-full text-sm text-surface-400 truncate">
                        {{ $t('public.team_capital') }}:
                        <span class="font-semibold text-primary">{{ formatAmount(parent.total_downline_capital_fund) }}</span>
                    </div>
                </div>

                <div class="pb-2 px-3 rounded-b grid grid-cols-2 gap-3 w-full self-stretch text-sm">
                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                        <span class="font-medium">{{ formatAmount(parent.total_directs, 0, '') }}</span>
                        <span class="text-xs uppercase">{{ $t('public.directs') }}</span>
                    </div>
                    <div class="flex flex-col items-center w-full bg-surface-100 dark:bg-surface-700 p-2">
                        <span class="font-medium">{{ formatAmount(parent.total_downlines, 0, '') }}</span>
                        <span class="text-xs uppercase">{{ $t('public.networks') }}</span>
                    </div>
                </div>

                <div class="pb-2 px-3">
                    <Button
                        type="button"
                        :severity="selectedReferral.id === parent.id ? 'info' : 'secondary'"
                        @click="selectReferral(parent)"
                        class="w-full"
                        size="small"
                        :outlined="selectedReferral.id !== parent.id"
                        :label="selectedReferral.id === parent.id ? $t('public.viewing_referral') : $t('public.view_referral_downlines')"
                    />
                </div>
            </div>
        </div>
    </div>
</template>