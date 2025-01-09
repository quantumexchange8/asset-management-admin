<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import { IconXboxX } from '@tabler/icons-vue';
import { IconX } from '@tabler/icons-vue';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import { IconSearch } from '@tabler/icons-vue';
import { IconAdjustments } from '@tabler/icons-vue';
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref, watch, watchEffect } from 'vue';
import debounce from "lodash/debounce.js";
import dayjs from 'dayjs';
import MemberTableAction from './MemberTableAction.vue';
import ProgressSpinner from 'primevue/progressspinner';
import { usePage } from '@inertiajs/vue3';
import Popover from 'primevue/popover';
import Card from 'primevue/card';

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const users = ref([]);
const totalRecords = ref(0);

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    country: { value: null, matchMode: FilterMatchMode.EQUALS },
    rank: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
});

//get user data
const lazyParams = ref({}); //track table parameters that need to be sendt to backend

const loadLazyData = (event) => { // event will retrieve from the datatable attribute
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value }; //...lazyParams.value(retain existing properties after update);

    try{
        setTimeout(async () => {

            //pagination, filter, sorting detail done by user through the event are pass into the params
            const params = { //define query parameters for API
                page: JSON.stringify(event?.page + 1), //retrieve page number from the event then send to BE
                sortField: event?.sortField, 
                sortOrder: event?.sortOrder,
                include: [], //an empty array for additional query parameters
                lazyEvent: JSON.stringify(lazyParams.value), //contain information about pagination, filtering, sorting
            };

            //send sorting/filter detail to BE
            const url = route('member.getMemberData', params);
            const response = await fetch(url);

            //BE send back result back to FE
            const results = await response.json();
            users.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            isLoading.value = false;
        }, 100);
    } catch (e) {
        users.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};

// get users filter,paginate,sorting input and pass to the event of lazyParams into each const then to loadLazyData
const onPage = (event) => {
    lazyParams.value = event;
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

//Date Filter
const selectedDate = ref([]);

const clearDate = () => {
    selectedDate.value = [];
};

watch(selectedDate, (newDateRange) => {
    if(Array.isArray(newDateRange)) { //ensure is an array with both start and end date
        const [startDate, endDate] = newDateRange; // extract data from newDateRange
        filters.value['start_date'].value = startDate; //update new date selected
        filters.value['end_date'].value = endDate;

        if(startDate !== null && endDate !== null){
            loadLazyData();
        }
    } else {
        console.warn('Invalid date range format:', newDateRange);
    }
});

//Country Filter
const countries = ref();
const loadingCountries = ref(false);

const getCountries = async () => {
    loadingCountries.value = true;
    try {
        const response = await axios.get('/get_countries');
        countries.value = response.data.countries;
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingCountries.value = false;
    }
};

//Rank Filter
const ranks = ref();
const loadingRanks = ref(false);

const getRanks = async () => {
    loadingRanks.value = true;
    try{
        const response = await axios.get('/get_ranks');
        ranks.value = response.data.ranks;
    } catch (error){
        console.error('Error fetching ranks:', error);
    } finally {
        loadingRanks.value = false;
    }
};

//KYC status filter
const kycStatus = ref(['unverified', 'verified', 'pending']);

//filter toggle
const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
    getCountries();
    getRanks();
};

//set a initial parameters when page first loaded then call loadLazyData to send initial parameters to BE
onMounted(() => {
    lazyParams.value = {
        first: dt.value.first,
        rows: dt.value.rows,
        sortField: null,
        sortOrder: null,
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
)

//ensure table data is updated dynamically to reflect filter changes (immediate trigger after changes)
watch([filters.value['country'], filters.value['rank'], filters.value['status']], () => {
    loadLazyData()
});

//clear all selected filter
const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['start_date'].value = null;
    filters.value['end_date'].value = null;
    filters.value['country'].value = null;
    filters.value['rank'].value = null;
    filters.value['status'].value = null;


    selectedDate.value = [];
};

//clear global filter
const clearFilterGlobal = () => {
    filters.value['global'].value = null;
};

//status severity
const getSeverity = (status) => {
    switch (status) {
        
        case 'verified':
            return 'success';

        case 'unverified':
            return 'danger';

        case 'pending':
            return 'info';

        case 'rejected':
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
    <Card>
        <template #content>
            <div class="w-full">
                <DataTable
                    :value="users" 
                    lazy
                    paginator 
                    removableSort
                    :rows="10" 
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    :first="first"
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                    v-model:filters="filters"
                    ref="dt"
                    dataKey="id"
                    :loading="isLoading"
                    :totalRecords="totalRecords"
                    @page="onPage($event)"
                    @sort="onSort($event)"
                    @filter="onFilter($event)"
                    :globalFilterFields="['name', 'email', 'username']"
                >
                    <template #header>
                        <div class="flex flex-wrap justify-between items-center">
                            <div class="flex items-center space-x-4 w-full md:w-auto">
                                
                                <!-- Search bar -->
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
                        </div>
                    </template>

                    <template #empty>
                        <div class="flex flex-col">
                            <span>No users</span>
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">Loading user data. Please wait. </span>
                        </div>
                    </template>
                    
                    <template v-if="users?.length > 0">
                        <Column
                            field="created_at"
                            style="min-width: 9rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">joined</span>
                            </template>
                            <template #body="{ data }">
                                {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                            </template>
                        </Column>
                        
                        <Column
                            field="name"
                            style="min-width: 12rem"
                            sortable
                            frozen
                        >

                            <template #header>
                                <span class="block">name</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.name }}
                            </template>

                        </Column>

                        <Column
                            field="email"
                            style="min-width: 12rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">email</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.email }}
                            </template>
                        </Column>

                        <Column
                            field="upline_id"
                            style="min-width: 12rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">referrer</span>
                            </template>

                            <template #body="{data}">
                                <div
                                    v-if="data.upline"
                                    class="flex flex-col items-start"
                                >
                                    <div class="font-medium max-w-[180px] truncate">
                                        {{ data.upline.name }}
                                    </div>
                                    <div class="text-gray-500 text-xs max-w-[180px] truncate">
                                        {{ data.upline.email }}
                                    </div>
                                </div>
                                <div v-else>
                                    -
                                </div>
                            </template>                
                        </Column>

                        <Column
                            field="setting_rank_id"
                            style="min-width: 10rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">rank</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.rank.rank_name }}
                            </template>
                        </Column>

                        <Column
                            field="role"
                            style="min-width: 10rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">role</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.role }}
                            </template>
                        </Column>

                        <Column
                            field="country_id"
                            style="min-width: 12rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">country</span>
                            </template>
                            <template #body="{data}">
                                <span>{{ data.country.name }}</span>             
                            </template>
                        </Column>

                        <Column
                            field="kyc_status"
                            sortable
                        >
                            <template #header>
                                <span class="block">status</span>
                            </template>
                            <template #body="{ data }">
                                <Tag :value="data.kyc_status" :severity="getSeverity(data.kyc_status)" />
                            </template>
                        </Column>

                        <Column
                            field="action"
                            frozen
                            alignFrozen="right"
                            header=""
                            style="width: 5%"
                            class="hidden md:table-cell"
                        >
                            <template #body="{data}">
                                <MemberTableAction :member="data"/>
                            </template>
                        </Column>
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>
   

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <!-- Filter Date -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    Filter By Date
                </div>
                <div class="relative w-full">
                    <DatePicker
                        v-model="selectedDate"
                        dateFormat="dd/mm/yy"
                        selectionMode="range"
                        placeholder="dd/mm/yyyy - dd/mm/yyyy"
                        class="w-full"
                    />
                    <div
                        v-if="selectedDate && selectedDate.length > 0"
                        class="absolute top-2/4 -mt-2 right-2 text-gray-400 select-none cursor-pointer bg-transparent"
                        @click="clearDate"
                    >
                        <IconX :size="15" strokeWidth="1.5"/>
                    </div>
                </div>
            </div>

            <!-- Filter Country -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    Filter By Country
                </div>
                <Select
                    v-model="filters['country'].value"
                    :options="countries"
                    optionLabel="name"
                    placeholder="Select Country"
                    filter
                    :filter-fields="['name']"
                    :loading="loadingCountries"
                    class="w-full"
                    showClear
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="flex items-center">
                            {{ slotProps.value.name }}
                        </div>
                        <span v-else>{{ slotProps.placeholder }}</span>
                    </template>
                    <template #option="slotProps">
                        <div>{{ slotProps.option.name }}</div>
                    </template>
                </Select>
            </div>

            <!-- Filter Rank -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    Filter By Rank
                </div>
                <Select
                    v-model="filters['rank'].value"
                    :options="ranks"
                    optionLabel="rank_name"
                    placeholder="Select Rank"
                    filter
                    :filter-fields="['rank_name']"
                    :loading="loadingRanks"
                    class="w-full"
                    showClear
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="flex items-center">
                            {{ slotProps.value.rank_name }}
                        </div>
                        <span v-else>{{ slotProps.placeholder }}</span>
                    </template>
                    <template #option="slotProps">
                        <div>{{ slotProps.option.rank_name }}</div>
                    </template>
                </Select>
            </div>

            <!-- Filter kyc Status -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    Filter By Status
                </div>
                <Select
                    v-model="filters['status'].value"
                    :options="kycStatus"
                    placeholder="Select Status"
                    :loading="loadingRanks"
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
</template>