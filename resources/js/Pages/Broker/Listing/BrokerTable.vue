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
import { IconPremiumRights, IconProgressCheck, IconSearch, IconUserDollar, IconXboxX, IconAdjustments } from '@tabler/icons-vue';
import AddBroker from './AddBroker.vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import debounce from "lodash/debounce.js";
import { usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import BrokerTableAction from './Partial/BrokerTableAction.vue';
import ToggleBrokerStatus from './Partial/ToggleBrokerStatus.vue';

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

const onSort = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};

const onFilter = (event) => {
    lazyParams.value.fitlers = filters.value;
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
        loadLazyData({ first: first.value, rows: rows.value });
    }
});
</script>

<template>
   <div class="flex justify-between items-center w-full">
        <div class="flex items-center space-x-4 w-full md:w-auto">
            <IconField>
                <InputIcon>
                    <IconSearch :size="16" stroke-width="1.5" />
                </InputIcon>
                <InputText 
                    v-model="filters['global'].value" 
                    placeholder="Keyword Search" 
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
            <!-- filter button -->
            <Button
                class="w-full md:w-28 flex gap-2"
                outlined
                @click="toggle"
            >
                <IconAdjustments :size="15"/>
                Filter
            </Button>
        </div>
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

    <div v-if="isLoading" class="grid grid-cols-1 xl:grid-cols-2 gap-5 self-stretch mx-auto max-w-[1920px]">
        <Card 
            style="overflow: hidden"
            v-for="index in props.brokerCounts"
        >
            <template #content>
                <div class="flex flex-col gap-4 items-center self-stretch">
                    <!-- Image moves on top on smaller screens -->
                    <div class="w-full flex flex-col sm:flex-row items-center gap-4 self-stretch">
                        <Skeleton width="30rem" height="4rem"/>

                        <div class="flex flex-col items-center sm:items-start w-full text-center sm:text-left">
                            <div class="self-stretch truncate text-gray-950 dark:text-white font-bold text-xl">
                                <Skeleton class="mt-2" width="6rem"/>
                            </div>
                            <div class="self-stretch truncate text-gray-500 text-sm">
                                <Skeleton class="mt-2" width="15rem"/>
                            </div>
                        </div>
                    </div>

                    <Divider />

                    <!-- Bottom Section -->
                    <div class="flex items-end justify-between self-stretch">
                        <div class="flex flex-col items-center gap-1 self-stretch w-full">
                            <div class="py-1 flex items-center gap-3 self-stretch">
                                <IconUserDollar size="20" stroke-width="1.25" />
                                <Skeleton width="5rem" />
                            </div>
                            <div class="py-1 flex items-center gap-3 self-stretch">
                                <IconPremiumRights size="20" stroke-width="1.25" />
                                <Skeleton width="5rem" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>

   
    <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-5 self-stretch mx-auto max-w-[1920px]">
        <Card 
            @filter="onFilter($event)"
            @sort="onSort($event)"
            class="w-full relative"
            style="overflow: hidden; border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);"
            v-for="broker in brokers"
            :key="broker.id"
        >
            <template #content>
                <div class="flex flex-col gap-4 items-center self-stretch">

                    <!-- Always in the top-right corner -->
                    <div class="absolute top-5 right-4">
                        <BrokerTableAction 
                            :broker="broker"
                            :locales="props.locales"
                        />
                    </div>

                    <!-- Image moves on top on smaller screens -->
                    <div class="w-full flex flex-col sm:flex-row items-center gap-4 self-stretch">
                        <div class="relative w-[200px] h-[40px] sm:w-[300px] sm:h-[75px] md:w-[400px] md:h-[100px] lg:w-[450px] lg:h-[100px] xl:w-[300px] xl:h-[80px] overflow-hidden">
                            <img 
                                alt="broker image" 
                                :src="broker.broker_image" 
                                class="w-full h-full object-contain"
                            />
                        </div>
                        
                        <div class="flex flex-col items-center sm:items-start w-full text-center sm:text-left">
                            <div class="self-stretch truncate text-gray-950 dark:text-white font-bold text-xl">
                                {{ broker.name }}
                            </div>
                            <div class="self-stretch truncate text-gray-500 text-sm">
                                Last Updated on: {{ dayjs(broker.updated_at).format('YYYY-MM-DD') }}  
                                {{ dayjs(broker.updated_at).add(8, 'hour').format('hh:mm:ss A') }}
                            </div>
                        </div>
                    </div>

                    <Divider />

                    <!-- Bottom Section -->
                    <div class="flex items-end justify-between self-stretch">
                        <div class="flex flex-col items-center gap-1 self-stretch w-full">
                            <div class="py-1 flex items-center gap-3 self-stretch">
                                <IconUserDollar size="20" stroke-width="1.25"/>
                                <div class="text-gray-950 dark:text-white text-sm font-medium">
                                    0 Investor
                                </div>
                            </div>
                            <div class="py-1 flex items-center gap-3 self-stretch">
                                <IconPremiumRights size="20" stroke-width="1.25"/>
                                <div class="text-gray-950 dark:text-white text-sm font-medium">
                                    0.00 Fund Capital
                                </div>
                            </div>
                            <div class="py-1 flex items-center gap-3 self-stretch">
                                <IconProgressCheck size="20" stroke-width="1.25"/>
                                <div class="text-gray-950 dark:text-white text-sm font-medium">
                                    <ToggleBrokerStatus 
                                        :broker="broker"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>

        <Popover ref="op">
            <div class="flex flex-col gap-6 w-60">
                <!-- Filter kyc Status -->
                <div class="flex flex-col gap-2 items-center self-stretch">
                    <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                        Filter By Status
                    </div>
                    <Select
                        v-model="filters['status'].value"
                        :options="brokerStatus"
                        placeholder="Select Status"
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
                Clear All
                </Button>
            </div>
        </Popover>
    </div>

    <Paginator 
        v-model:rows="rows"
        v-model:first="first"
        :totalRecords="totalRecords" 
        :rowsPerPageOptions="[5, 10, 20, 30]"
        @page="onPage"
        template="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
        lazy
        ref="dt"
    >
    
    </Paginator>
</template>