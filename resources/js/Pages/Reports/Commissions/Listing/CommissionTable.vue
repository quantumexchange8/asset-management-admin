<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import ProgressSpinner from 'primevue/progressspinner';
import Popover from 'primevue/popover';
import Card from 'primevue/card';
import { IconXboxX, IconX, IconSearch, IconAdjustments, IconDownload } from '@tabler/icons-vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import debounce from "lodash/debounce.js";
import dayjs from 'dayjs';
import { FilterMatchMode } from '@primevue/core/api';
import Import from './Import.vue';
import { usePage } from '@inertiajs/vue3';

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const commissions = ref([]);
const totalRecords = ref(0);

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
});

//get commission data
const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value};

    try {
        setTimeout(async () => {

            const params = {
                page: JSON.stringify(event?.page + 1),
                sortField: event?.sortField,
                sortOrder: event?.sortOrder,
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value),
            };

            const url = route('report.getCommissionData', params);
            const response = await fetch(url);

            const results = await response.json();
            commissions.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            isLoading.value = false;
        }, 100);
    } catch(e) {
        commissions.value = [];
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
    lazyParams.value.filters = filters.value;
    loadLazyData(event);
}

//date filter
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

//status filter
const status = ref(['calculated', 'pending']);

//filter toggle
const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
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
watch([filters.value['status']], () => {
    loadLazyData();
});

//clear all selected filter
const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['start_date'].value = null;
    filters.value['end_date'].value = null;
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
        
        case 'calculated':
            return 'success';

        case 'pending':
            return 'danger';
    }
};

//export button
const exportTable = ref('no');

const exportStatus = ref(false);
const exportCommission = () => {
    exportStatus.value = true;
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value };

    const params = {
        page: JSON.stringify(event?.page + 1),
        sortField: event?.sortField,
        sortOrder: event?.sortOrder,
        include: [],
        lazyEvent: JSON.stringify(lazyParams.value),
        exportStatus: true,
    };

    const url = route('report.getCommissionData', params);

    try {
        window.location.href = url;
    } catch (e) {
        console.error('Error occured during export:', e);
    } finally {
        isLoading.value = false;
        exportStatus.value = false;
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
                    :value="commissions" 
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
                    :globalFilterFields="['email']"
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

                            <div class="flex items-center space-x-4 w-full md:w-auto mt-4 md:mt-0">
                                <!-- Export button -->
                                <Button 
                                    class="w-full md:w-auto flex justify-center items-center" 
                                    @click="exportCommission"
                                    :disabled="exportTable==='yes'"
                                >
                                    <span class="pr-1">Export</span>
                                    <IconDownload size="16" stroke-width="1.5"/>
                                </Button>
                            
                               <Import />
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div class="flex flex-col">
                            <span>No Commission</span>
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span v-if="exportTable === 'no'" class="text-sm text-gray-700 dark:text-gray-300">Loading commission data. Please wait. </span>
                            <span v-else class="text-sm text-gray-700 dark:text-gray-300">Exporting Commission</span>
                        </div>
                    </template>

                    <template v-if="commissions?.length > 0">
                        <Column
                            field="email"
                            style="min-width: 9rem"
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
                            field="broker_id"
                            style="min-width: 9rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">broker</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.broker_id }}
                            </template>
                        </Column>

                        <Column
                            field="volume"
                            style="min-width: 9rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">lot size</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.volume }}
                            </template>
                        </Column>

                        <Column
                            field="date"
                            style="min-width: 9rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">date</span>
                            </template>
                            <template #body="{ data }">
                                {{ dayjs(data.date).format('YYYY-MM-DD') }}
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ dayjs(data.date).add(8, 'hour').format('hh:mm:ss A') }}
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="status"
                            style="min-width: 9rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">status</span>
                            </template>
                            <template #body="{ data }">
                                <Tag :value="data.status" :severity="getSeverity(data.status)" />
                            </template>
                        </Column>

                        <Column
                            field="action"
                            style="min-width: 7rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">action</span>
                            </template>
                            <template #body="{ data }">
                           
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

            <!-- Filter Status -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    Filter By Status
                </div>
                <Select
                    v-model="filters['status'].value"
                    :options="status"
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
</template>