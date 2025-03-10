<script setup>
import Card from 'primevue/card';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import ProgressSpinner from 'primevue/progressspinner';
import { IconXboxX, IconSearch, IconPlus, IconMinus } from '@tabler/icons-vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { usePage, Link } from '@inertiajs/vue3';
import debounce from "lodash/debounce.js";
import { generalFormat } from '@/Composables/format';
import { useLangObserver } from '@/Composables/localeObserver';
import ReferralDetail from './Detail/ReferralDetail.vue';
import ReferralDownline from './Partial/ReferralDownline.vue';

const { formatAmount } = generalFormat();
const { locale } = useLangObserver();
const isLoading = ref(false);
const referrals = ref([]);
const expandedUsers = ref({});

// Function to toggle top-level user expansion
const toggleExpand = (id) => {
    expandedUsers.value = { ...expandedUsers.value, [id]: !expandedUsers.value[id] };
};

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const lazyParams = ref({}); //track table parameters that need to be send to backend

const loadLazyData = (event) => { // event will retrieve from the datatable attribute
    isLoading.value = true;
    referrals.value = [];
    expandedUsers.value = {};
    lazyParams.value = {
        ...lazyParams.value,
        first: event?.first || 0,
        filters: filters.value
    };

    try {
        setTimeout(async () => {

            //pagination, filter, sorting detail done by user through the event are pass into the params
            const params = { //define query parameters for API
                include: [], //an empty array for additional query parameters
                lazyEvent: JSON.stringify(lazyParams.value), //contain information about pagination, filtering, sorting
            };

            //send sorting/filter detail to BE
            const url = route('referral.getReferralData', params);
            const response = await fetch(url);

            //BE send back result back to FE
            const results = await response.json();
            referrals.value = results?.data?.referrals || [];
            isLoading.value = false;
        }, 100);
    } catch (e) {
        referrals.value = [];
        isLoading.value = false;
    }
};

//set a initial parameters when page first loaded then call loadLazyData to send initial parameters to BE
onMounted(() => {
    lazyParams.value = {
        first: 0,
        filters: filters.value
    };
    loadLazyData();
});

//monitor changes to global filter, debounce ensure loadLazyData tiggered 300ms after user stop typing
watch(
    () => filters.value['global'].value,
    debounce(() => {
        expandedUsers.value = {}; // Collapse all expanded users before searching
        loadLazyData();
    }, 300)
);


//clear global filter
const clearFilterGlobal = () => {
    filters.value['global'].value = null;
};

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});
</script>
<template>
    <Card>
        <template #content>
            <div class="w-full">
                <div class="flex flex-wrap justify-between items-center">
                    <div class="flex items-center space-x-4 w-full md:w-auto">
                        <!-- Search bar -->
                        <IconField>
                            <InputIcon>
                                <IconSearch :size="16" stroke-width="1.5" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" :placeholder="$t('public.search_keyword')"
                                type="text" class="block w-full pl-10 pr-10" />
                            <!-- Clear filter button -->
                            <div v-if="filters['global'].value"
                                class="absolute top-1/2 -translate-y-1/2 right-4 text-surface-300 hover:text-surface-400 select-none cursor-pointer"
                                @click="clearFilterGlobal">
                                <IconXboxX aria-hidden="true" :size="15" />
                            </div>
                        </IconField>
                    </div>
                </div>

                <div v-if="isLoading" class="flex flex-col gap-2 items-center justify-center">
                    <ProgressSpinner />
                    <span class="text-sm text-surface-700 dark:text-surface-300">{{ $t('public.referral_loading_caption') }}</span>
                </div>

                <div v-else class="flex flex-col gap-1 self-stretch relative pt-4 overflow-x-auto">
                    <div class="flex flex-col gap-4">
                        <template v-for="user in referrals" :key="user.id">
                            <div :class="[
                                'p-4 rounded-lg bg-surface-100 dark:bg-surface-800 flex items-center',
                                locale === 'en' ? 'max-w-[1190px] min-w-[1190px]' : 'max-w-[950px] min-w-[950px]',
                            ]">
                                <div class="flex items-center w-full relative gap-6">
                                    <!-- Left: Expand Button -->
                                    <Button
                                        v-if="user.children?.length"
                                        @click="toggleExpand(user.id)"
                                        class="w-10 h-10 flex-shrink-0"
                                        rounded
                                    >
                                        <IconPlus v-if="!expandedUsers[user.id]" size="20" stroke-width="2"/>
                                        <IconMinus v-else size="20" stroke-width="2"/>
                                    </Button>

                                    <!-- Level Indicator -->
                                    <div class="w-8 h-8 flex items-center justify-center bg-blue-500 text-white font-semibold rounded-full text-sm">
                                        1
                                    </div>
                                    
                                    <!-- Grid for User Info & Other Details -->
                                    <div :class="[
                                       'gap-x-6 gap-y-2 text-sm pr-10',
                                       locale === 'en' ? 'grid grid-cols-[1.5fr_1fr_2fr_2fr_1fr_1fr]' : 'grid grid-cols-[3fr_1.5fr_2.5fr_2.5fr_1fr_1fr]',
                                    ]">
                                        <!-- Name & Email -->
                                        <div class="flex flex-col items-start">
                                            <span class="font-medium text-surface-950 dark:text-white truncate">{{ user.name }}</span>
                                            <span class="text-surface-500 text-sm truncate">{{ user.email }}</span>
                                        </div>

                                        <!-- Rank -->
                                        <div class="flex flex-col items-start">
                                            <span class="font-semibold">{{ $t('public.rank') }}</span>
                                            <Tag
                                                severity="secondary"
                                                :value="user.rank.rank_name === 'member' ? $t(`public.${user.rank.rank_name}`) : user.rank.rank_name"
                                            />
                                        </div>

                                        <!-- Personal Fund -->
                                        <div class="flex flex-col items-start">
                                            <span class="font-semibold">{{ $t('public.personal_capital_fund') }} ($)</span>
                                            <span class="dark:text-surface-400">{{ formatAmount(user.total_personal_fund) }}</span>
                                        </div>

                                        <!-- Team Fund -->
                                        <div class="flex flex-col items-start">
                                            <span class="font-semibold">{{ $t('public.team_capital_fund') }} ($)</span>
                                            <span class="dark:text-surface-400">{{ formatAmount(user.total_team_fund) }}</span>
                                        </div>

                                        <!-- Direct Downlines -->
                                        <div class="flex flex-col items-start whitespace-nowrap">
                                            <span class="font-semibold">{{ $t('public.direct_downlines') }}</span>
                                            <span class="dark:text-surface-400">{{ user.downlines_count }}</span>
                                        </div>

                                        <div class="flex flex-col items-start">
                                            <ReferralDetail :referral="user" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Transition
                                enter-active-class="transition-all duration-300 ease-in-out"
                                enter-from-class="opacity-0 translate-y-2 scale-y-95"
                                enter-to-class="opacity-100 translate-y-0 scale-y-100"
                                leave-active-class="transition-all duration-200 ease-in-out"
                                leave-from-class="opacity-100 translate-y-0 scale-y-100"
                                leave-to-class="opacity-0 translate-y-2 scale-y-95"
                            >
                                <!-- Show downlines only when expanded -->
                                <div v-if="expandedUsers[user.id]" class="pl-8 border-l border-surface-300 -mt-4">
                                    <ReferralDownline 
                                        :downlines="user.children" 
                                        :expandedUsers="expandedUsers" 
                                        @update:expandedUsers="expandedUsers = $event" 
                                        :level="2" 
                                    />
                                </div>
                            </Transition>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
