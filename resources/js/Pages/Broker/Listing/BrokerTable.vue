<script setup>
import Card from 'primevue/card';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Paginator from 'primevue/paginator';
import Button from 'primevue/button';
import Skeleton from 'primevue/skeleton';
import { IconSearch, IconXboxX } from '@tabler/icons-vue';
import AddBroker from './AddBroker.vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import debounce from "lodash/debounce.js";
import { usePage, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const props = defineProps({
    brokerCounts: Number,
});

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const rows = ref(5);
const brokers = ref([]);
const totalRecords = ref(0);

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

//clear global filter
const clearFilterGlobal = () => {
    filters.value['global'].value = null;
};

const lazyParams = ref({});

const loadLazyData = (event) => { // event will retrieve from the datatable attribute
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value, rows: event?.rows || rows.value  }; //...lazyParams.value(retain existing properties after update);

    try {
        setTimeout(async () => {

            //pagination, filter, sorting detail done by user through the event are pass into the params
            const params = { //define query parameters for API
                page: JSON.stringify(event?.page + 1),
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

const onFilter = (event) => {
    lazyParams.value.fitlers = filters.value;
    loadLazyData(event);
};

onMounted(() => {
    lazyParams.value = {
        first: first.value, // Start from the first record
        rows: rows.value, // Rows per page
        filters: filters.value,
    };
    loadLazyData({ first: first.value, rows: rows.value });
});

watch(
    filters.value['global'],
    debounce(() => {
        loadLazyData({ first: first.value, rows: rows.value });
    }, 300)
)

watchEffect(() => {
    if (usePage().props.toast !== null) {
        loadLazyData({ first: first.value, rows: rows.value });
    }
});
</script>

<template>
   <div class="flex justify-between items-center w-full">
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

        <div class="ml-4">
            <AddBroker />
        </div>
    </div>

    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 w-full gap-4 md:gap-5">
        <Card 
            style="overflow: hidden"
            v-for="index in props.brokerCounts"
        >
            <template #title>
                <Skeleton class="w-full h-6 mt-2" />
            </template>

            <template #subtitle>
                <Skeleton class="w-3/4 h-4 mt-2" />
            </template>

            <template #footer>
                <div class="flex gap-4 mt-1">
                    <Skeleton class="w-full h-8" />
                    <Skeleton class="w-full h-8" />
                </div>
            </template>
        </Card>
    </div>

   
    <div v-else class="grid grid-cols-1 md:grid-cols-2 w-full gap-4 md:gap-5">

        <Card 
            @filter="onFilter($event)"
            style=" overflow: hidden"
            v-for="broker in brokers"
            :key="broker.id"

        >
            <template #header>
                <img alt="user header" :src="broker.broker_image" class="w-full h-16 sm:h-24 md:h-16 lg:h-16 2xl:h-24 object-contain mt-4" />
            </template>

            <template #title>
                {{ broker.name }}
            </template>

            <template #subtitle>
                Last Updated on: {{ dayjs(broker.updated_at).format('YYYY-MM-DD') }}  {{ dayjs(broker.updated_at).add(8, 'hour').format('hh:mm:ss A') }}
            </template>

            <template #footer>
                <div class="flex gap-4 mt-1">
                    <Link
                        :href="route('broker.detail.brokerDetail', broker.id)"
                        class="w-full"
                    >
                        <Button severity="secondary" class="w-full">
                            View
                        </Button>
                    </Link>
                    <Button severity="danger" class="w-full">
                        Remove
                    </Button>
                </div>
            </template>
        </Card>
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