<script setup>
import Card from 'primevue/card';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Paginator from 'primevue/paginator';
import Button from 'primevue/button';
import Divider from 'primevue/divider';
import Skeleton from 'primevue/skeleton';
import Tag from 'primevue/tag';
import Popover from 'primevue/popover';
import {
    IconPremiumRights,
    IconSearch,
    IconUserDollar,
    IconXboxX,
    IconAdjustments,
    IconCircleLetterB
} from '@tabler/icons-vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import debounce from "lodash/debounce.js";
import { usePage } from '@inertiajs/vue3';
import BrokerTableAction from './Partial/BrokerTableAction.vue';
import ToggleBrokerStatus from './Partial/ToggleBrokerStatus.vue';
import EmptyData from "@/Components/EmptyData.vue";
import {generalFormat} from "@/Composables/format.js";

const props = defineProps({
    brokerCounts: Number,
    locales: Array,
});

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const rows = ref(5);
const brokers = ref([]);
const totalRecords = ref(0);
const {formatAmount} = generalFormat();

const selectedSort = ref('latest');

const sortOptions = ref([
    'latest',
    'largest_fund',
    'most_investors',
]);

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const lazyParams = ref({});

const loadLazyData = (event) => { // event will retrieve from the datatable attribute
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value, rows: event?.rows || rows.value  }; //...lazyParams.value(retain existing properties after update);

    try {
        setTimeout(async () => {

            //pagination, filter, sorting detail done by user through the event are pass into the params
            const params = { //define query parameters for API
                page: JSON.stringify(event?.page + 1),
                sortOrder: event?.sortOrder,
                include: [], //an empty array for additional query parameters
                lazyEvent: JSON.stringify(lazyParams.value), //contain information about pagination, filtering, sorting
            };

            //send sorting/filter detail to BE
            const url = route('broker.getBrokerData', params);
            const response = await fetch(url);

            //BE send back result back to FE
            const results = await response.json();
            brokers.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            isLoading.value = false;
        }, 100);
    } catch (e) {
        brokers.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};

const onPage = (event) => {
    // Update the starting index for pagination
    first.value = event.first;
    rows.value = event.rows;
    loadLazyData(event);
};

//status filter
const brokerStatus = ref(['active', 'inactive']);

const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
};

onMounted(() => {
    lazyParams.value = {
        first: first.value, // Start from the first record
        rows: rows.value, // Rows per page
        sortOrder: selectedSort.value,
        filters: filters.value,
    };
    loadLazyData({ first: first.value, rows: rows.value });
});

watch(selectedSort, (newSort) => {
    lazyParams.value.sortOrder = newSort;
    loadLazyData({ first: first.value, rows: rows.value, sortOrder: newSort });
});

watch(
    filters.value['global'],
    debounce(() => {
        loadLazyData({ first: first.value, rows: rows.value });
    }, 300)
)

watch([ filters.value['status']], () => {
    loadLazyData();
});

//clear all selected filter
const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['status'].value = null;
};

//clear global filter
const clearFilterGlobal = () => {
    filters.value['global'].value = null;
};

//status severity
const getSeverity = (status) => {
    switch (status) {

        case 'active':
            return 'success';

        case 'inactive':
            return 'danger';
    }
};

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData();
    }
});
</script>

<template>
    <div class="flex flex-col md:flex-row gap-3 items-center self-stretch">
        <div class="relative w-full md:w-60">
            <IconField>
                <InputIcon>
                    <IconSearch :size="16" stroke-width="1.5" />
                </InputIcon>
                <InputText
                    v-model="filters['global'].value"
                    :placeholder="$t('public.search_keyword')"
                    type="text"
                    class="block w-full pl-10 pr-10"
                />
                <!-- Clear filter button -->
                <div
                    v-if="filters['global'].value"
                    class="absolute top-1/2 -translate-y-1/2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                    @click="clearFilterGlobal"
                >
                    <IconXboxX aria-hidden="true" :size="15" />
                </div>
            </IconField>
        </div>

        <div class="w-full flex justify-between items-center self-stretch gap-3">
            <!-- filter button -->
            <Button
                class="w-full md:w-28 flex items-center gap-2"
                outlined
                @click="toggle"
                size="small"
            >
                <IconAdjustments :size="16" stroke-width="1.5" />
                {{ $t('public.filter') }}
            </Button>

            <Select
                class="w-full md:w-56"
                v-model="selectedSort"
                :options="sortOptions"
                optionLabel="name"
                :placeholder="'Sort'"
            >
                <template #value="slotProps">
                    <div v-if="slotProps.value" class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <div>{{ $t(`public.${slotProps.value}`) }}</div>
                        </div>
                    </div>
                </template>
                <template #option="slotProps">
                    {{ $t(`public.${slotProps.option}`) }}
                </template>
            </Select>
        </div>
    </div>

    <div
        v-if="brokerCounts === 0"
        class="w-full"
    >
        <EmptyData
            :title="$t('public.no_broker_found')"
            :message="$t('public.no_broker_found_caption')"
        />
    </div>

    <div
        v-else
        class="w-full"
    >
        <div v-if="isLoading">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 self-stretch">
                <Card
                    v-for="(broker, index) in brokerCounts > 12 ? 12 : brokerCounts"
                    :key="index"
                >
                    <template #content>
                        <div class="flex flex-col items-center gap-4 self-stretch">
                            <!-- Profile Section -->
                            <div class="w-full flex items-center gap-4 self-stretch">
                                <div class="w-10 h-10 rounded-full grow-0 shrink-0 flex items-center justify-center border border-surface-200 dark:border-surface-800 text-surface-300 dark:text-surface-600">
                                    <IconCircleLetterB size="28" stroke-width="1.5" />
                                </div>
                                <div class="flex flex-col items-start">
                                    <Skeleton width="10rem" class="my-1"></Skeleton>
                                    <Skeleton width="5rem" class="mt-1"></Skeleton>
                                </div>
                            </div>

                            <!-- StatusBadge Section -->
                            <div class="flex items-center gap-2 self-stretch">
                                <Skeleton width="5rem" height="1.5rem"></Skeleton>
                            </div>

                            <!-- Details Section -->
                            <div class="flex items-end justify-between self-stretch">
                                <div class="flex flex-col items-center gap-1 self-stretch w-full">
                                    <div class="py-1 flex items-center gap-3 self-stretch w-full text-gray-500">
                                        <IconUserDollar size="20" stroke-width="1.5" />
                                        <Skeleton width="5rem"></Skeleton>
                                    </div>
                                    <div class="py-1 flex items-center gap-3 self-stretch text-gray-500">
                                        <IconPremiumRights size="20" stroke-width="1.5" />
                                        <Skeleton width="5rem"></Skeleton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <div v-else-if="!brokers.length">
            <EmptyData
                :title="$t('public.no_broker_found')"
                :message="$t('public.no_broker_found_caption')"
            />
        </div>

        <div v-else>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 self-stretch">
                <Card
                    v-for="(broker, index) in brokers"
                    :key="index"
                >
                    <template #content>
                        <div class="flex flex-col items-center gap-4 self-stretch">
                            <!-- Profile Section -->
                            <div class="w-full flex items-center gap-4 self-stretch">
                                <img
                                    v-if="broker.media"
                                    class="object-cover w-10 h-10 rounded-full"
                                    :src="broker.media[0].original_url"
                                    alt="broker_image"
                                />
                                <div
                                    v-else
                                    class="w-10 h-10 rounded-full grow-0 shrink-0 flex items-center justify-center border border-surface-200 dark:border-surface-800 text-surface-300 dark:text-surface-600"
                                >
                                    <IconCircleLetterB size="28" stroke-width="1.5" />
                                </div>
                                <div class="flex flex-col items-start w-full">
                                    <div class="self-stretch truncate text-gray-950 dark:text-white font-bold">
                                        {{ broker.name }}
                                    </div>
                                    <a
                                        :href="broker.url"
                                        target="_blank"
                                        class="text-sm text-surface-500 hover:text-blue-500 max-w-80 truncate"
                                    >
                                        {{ broker.url }}
                                    </a>
                                </div>
                                <div class="flex gap-3 items-center justify-end w-8">
                                    <BrokerTableAction
                                        :broker="broker"
                                        :locales="locales"
                                    />
                                </div>
                            </div>

                            <!-- StatusBadge Section -->
                            <div class="flex items-center gap-2 self-stretch">
                                <Tag
                                    :severity="broker.status === 'active' ? 'success' : 'danger'"
                                    :value="$t(`public.${broker.status}`)"
                                />
                            </div>

                            <!-- Details Section -->
                            <div class="flex items-end justify-between self-stretch">
                                <div class="flex flex-col items-center gap-1 self-stretch w-full">
                                    <div class="py-1 flex items-center gap-3 self-stretch w-full text-gray-500">
                                        <IconUserDollar size="20" stroke-width="1.5" />
                                        <div class="text-gray-950 dark:text-white text-sm font-medium">
                                            {{ formatAmount(broker.connections_count, 0) }} {{ $t('public.connections') }}
                                        </div>
                                    </div>
                                    <div class="py-1 flex items-center gap-3 self-stretch text-gray-500">
                                        <IconPremiumRights size="20" stroke-width="1.5" />
                                        <div class="text-gray-950 dark:text-white text-sm font-medium">
                                            <span class="text-primary-500">$ {{ formatAmount(Number(broker.connections_sum_capital_fund ?? 0)) }}</span> {{ $t('public.capital_fund') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
            <Paginator
                :first="first"
                :rows="rows"
                :totalRecords="totalRecords"
                @page="onPage"
            />
        </div>
    </div>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <!-- Filter kyc Status -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    {{ $t('public.filter_by_status') }}
                </div>
                <Select
                    v-model="filters['status'].value"
                    :options="brokerStatus"
                   :placeholder="$t('public.select_status')"
                    class="w-full"
                    showClear
                >
                    <template #option="slotProps">
                        <Tag :value="slotProps.option" :severity="getSeverity(slotProps.option)" />
                    </template>
                </Select>
            </div>

            <Button
                type="button"
                outlined
                class="w-full"
                @click="clearAll"
            >
            {{ $t('public.clear_all') }}
            </Button>
        </div>
    </Popover>
</template>
