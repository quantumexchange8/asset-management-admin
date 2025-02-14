<script setup>
import Card from 'primevue/card';
import TreeTable from 'primevue/treetable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import ProgressSpinner from 'primevue/progressspinner';
import { IconXboxX, IconX, IconSearch, IconAdjustments, IconFileDescription } from '@tabler/icons-vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { usePage, Link } from '@inertiajs/vue3';
import debounce from "lodash/debounce.js";
import { generalFormat } from '@/Composables/format';
import { useLangObserver } from '@/Composables/localeObserver';
import ReferralDetail from './Detail/ReferralDetail.vue';

const {formatAmount} = generalFormat();
const {locale} = useLangObserver();
const isLoading = ref(false);
const referrals = ref([]);
const dt = ref(null);
const first = ref(0);

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const lazyParams = ref({}); //track table parameters that need to be send to backend

const loadLazyData = (event) => { // event will retrieve from the datatable attribute
    isLoading.value = true;

    lazyParams.value = { 
        ...lazyParams.value, 
        first: event?.first || first.value, 
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
            console.log(results?.data?.referrals);
            isLoading.value = false;
        }, 100);
    } catch (e) {
        referrals.value = [];
        isLoading.value = false;
    }
};

const onFilter = (event) => {
    lazyParams.value.filters = filters.value;
    loadLazyData(event);
};

//set a initial parameters when page first loaded then call loadLazyData to send initial parameters to BE
onMounted(() => {
    lazyParams.value = {
        first: dt.value.first,
        filters: filters.value
    };
    loadLazyData();
});

//monitor changes to global filter, debounce ensure loadLazyData tiggered 300ms after user stop typing
watch(
    filters.value['global'],
    debounce(() => {
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
                <TreeTable 
                    :value="referrals" 
                    lazy
                    :first="first"
                    @filter="onFilter($event)"
                    v-model:filters="filters"
                    ref="dt"
                    dataKey="id"
                    :loading="isLoading"
                    :globalFilterFields="['name', 'email']"
                    scrollable
                    :resizableColumns="true"
                >
                    <template #header>
                        <div class="flex flex-wrap justify-between items-center">
                            <div class="flex items-center space-x-4 w-full md:w-auto">
                                <!-- Search bar -->
                                <IconField>
                                    <InputIcon>
                                        <IconSearch :size="16" stroke-width="1.5" />
                                    </InputIcon>
                                    <InputText v-model="filters['global'].value" placeholder="Keyword Search"
                                        type="text" class="block w-full pl-10 pr-10" />
                                    <!-- Clear filter button -->
                                    <div v-if="filters['global'].value"
                                        class="absolute top-1/2 -translate-y-1/2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                                        @click="clearFilterGlobal">
                                        <IconXboxX aria-hidden="true" :size="15" />
                                    </div>
                                </IconField>
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div class="flex flex-col">
                            <span>{{ $t('public.no_referral') }}</span>
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner strokeWidth="4" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Loading referral data. Please wait.
                            </span>
                        </div>
                    </template>

                    <template>
                        <Column 
                            field="name" 
                            style="min-width: 350px"
                            expander
                        >
                            <template #header>
                                <span class="block">{{ $t('public.name') }}</span>
                            </template>
                            <template #body="{ node }">
                                <div class="flex flex-col">
                                    <span class="text-surface-950 dark:text-white">{{ node.name }}</span> 
                                    <span class="text-surface-500 text-sm">{{ node.email }}</span> 
                                </div>
                            </template>
                        </Column>

                        <Column 
                            field="setting_rank_id"
                            :style="locale === 'cn' ? 'min-width: 0px' : 'min-width: 100px'"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.rank') }}</span>
                            </template>
                            <template #body="{ node }">
                                {{ node.rank.rank_name }}
                            </template>
                        </Column>

                        <Column 
                            field="total_personal_fund"
                            :style="locale === 'cn' ? 'min-width: 20px' : 'min-width: 240px'"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.personal_capital_fund') }} ($)</span>
                            </template>
                            <template #body="{ node }">
                                {{ formatAmount(node.total_personal_fund) }}
                            </template>
                        </Column>

                        <Column 
                            field="total_team_fund"
                             :style="locale === 'cn' ? 'min-width: 20px' : 'min-width: 200px'"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.team_capital_fund') }} ($)</span>
                            </template>
                            <template #body="{ node }">
                                {{ formatAmount(node.total_team_fund) }}
                            </template>
                        </Column>

                        <Column 
                            field="downlines_count"
                            style="min-width: 200px"
                            :style="locale === 'cn' ? 'min-width: 20px' : 'min-width: 190px'"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.direct_downlines') }}</span>
                            </template>
                            <template #body="{ node }">
                                {{ node.downlines_count }}
                            </template>
                        </Column>

                        <Column 
                            field="action"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.action') }}</span>
                            </template>
                            <template #body="{ node }">
                                <ReferralDetail
                                    :referral="node"
                                />
                            </template>
                        </Column>
                    </template>
                </TreeTable>
            </div>
        </template>
    </Card>
</template>