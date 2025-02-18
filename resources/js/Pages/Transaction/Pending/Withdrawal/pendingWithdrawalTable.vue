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
import PendingWithdrawalAction from './PendingWithdrawalAction.vue';
import EmptyData from '@/Components/EmptyData.vue';

const props = defineProps({
    pendingWithdrawalCounts: Number,
});

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const pendingWithdrawal = ref([]);
const totalRecords= ref(0);
const totalPendingAmount = ref();
const pendingWithdrawalCounts = ref();

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    fund_type: { value: null, matchMode: FilterMatchMode.EQUALS },
});

//get user data
const lazyParams = ref({}); //track table parameters that need to be send to backend

const loadLazyData = (event) => { // event will retrieve from the datatable attribute
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value }; //...lazyParams.value(retain existing properties after update);

    try {
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
            const url = route('transaction.pending.getPendingWithdrawalData', params);
            const response = await fetch(url);

            //BE send back result back to FE
            const results = await response.json();
            pendingWithdrawal.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            totalPendingAmount.value = results?.totalPendingAmount;
            pendingWithdrawalCounts.value = results?.pendingWithdrawalCounts;
            isLoading.value = false;
        }, 100);
    } catch (e) {
        pendingWithdrawal.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};

// get withdrawal filter,paginate,sorting input and pass to the event of lazyParams into each const then to loadLazyData
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

const emit = defineEmits(['updatePendingWithdrawal']);

// Emit the totals whenever they change
watch([totalPendingAmount, pendingWithdrawalCounts], () => {
    emit('updatePendingWithdrawal', {
        totalPendingAmount: totalPendingAmount.value,
        pendingWithdrawalCounts: pendingWithdrawalCounts.value,
    });
});


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

//filter fund type
const fundType = ref(['demo_fund','real_fund']);

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
watch([filters.value['fund_type']], () => {
    loadLazyData()
});

//clear all selected filter
const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['start_date'].value = null;
    filters.value['end_date'].value = null;
    filters.value['fund_type'].value = null;
    selectedDate.value = [];
};

//clear global filter
const clearFilterGlobal = () => {
    filters.value['global'].value = null;
};

//status severity
const getSeverity = (status) => {
    switch (status) {

        case 'processing':
            return 'info';
    }
};

//export button
const exportTable = ref('no');

const exportStatus = ref(false);
const exportWithdrawal = () => {
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

    const url = route('transaction.pending.getPendingWithdrawalData', params);

    try {
        window.location.href = url;
    } catch (e) {
        console.error('Error occured during export:', e);
    } finally {
        isLoading.value = false;
        exportStatus.value = false;
    }
};

// Define a method to refresh the table
const refreshTable = () => {
    loadLazyData();
};
</script>

<template>
    <Card>
        <template #content>
            <div class="w-full">
                <DataTable
                    :value="pendingWithdrawal"
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
                    :globalFilterFields="['user.name' ,'transaction_number']"
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

                                <!-- filter button -->
                                <Button
                                    class="w-full md:w-28 flex gap-2"
                                    outlined
                                    @click="toggle"
                                >
                                    <IconAdjustments :size="15"/>
                                    {{ $t('public.filter') }}
                                </Button>
                            </div>

                            <div class="flex items-center space-x-4 w-full md:w-auto mt-4 md:mt-0">
                                <!-- Export button -->
                                <Button
                                    class="w-full md:w-auto flex justify-center items-center"
                                    @click="exportWithdrawal"
                                    :disabled="exportTable==='yes'"
                                >
                                    <span class="pr-1">{{ $t('public.export') }}</span>
                                    <IconDownload size="16" stroke-width="1.5"/>
                                </Button>
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div v-if="pendingWithdrawalCounts === 0">
                            <EmptyData
                                :title="$t('public.no_withdrawal_founded')"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span v-if="exportTable === 'no'" class="text-sm text-gray-700 dark:text-gray-300">{{ $t('public.withdrawal_loading_caption') }}</span>
                            <span v-else class="text-sm text-gray-700 dark:text-gray-300">{{ $t('public.export_withdrawal_caption') }}</span>
                        </div>
                    </template>

                    <template v-if="pendingWithdrawal?.length > 0">
                        <Column
                            field="transaction_number"
                            style="min-width: 17rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.transaction_number') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.transaction_number }}
                            </template>
                        </Column>

                        <Column
                            field="user_id"
                            style="min-width: 12rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.name') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.user.name }}
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ data.user.email }}
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="user.upline"
                            style="min-width: 12rem"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.upline') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.user.upline?.name }}
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ data.user.upline?.email }}
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="fund_type"
                            style="min-width: 12rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.fund_type') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ $t(`public.${data.fund_type}`) }}
                            </template>
                        </Column>

                        <Column
                            field="from_wallet_id"
                            style="min-width: 12rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.from') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ $t(`public.${data.from_wallet?.type}`) || '-'}}
                            </template>
                        </Column>

                        <Column
                            field=""
                            style="min-width: 12rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.to') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.user.name }}
                            </template>
                        </Column>

                        <Column
                            field="payment_method"
                            style="min-width: 12rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.method') }}</span>
                            </template>
                            <template #body="{ data }">
                                crypto
                            </template>
                        </Column>
                        
                        <Column
                            field="amount"
                            style="min-width: 12rem"
                            dataType="numeric"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.amount') }}</span>
                            </template>
                            <template #body="{ data }">
                               $ {{ data.amount }}
                            </template>
                        </Column>

                        <Column
                            field="transaction_charges"
                            style="min-width: 12rem"
                            dataType="numeric"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.fee') }}</span>
                            </template>
                            <template #body="{ data }">
                                <span class="text-red-500">  $ {{ data.transaction_charges || '-'}}</span>
                            </template>
                        </Column>

                        <Column
                            field="transaction_amount"
                            style="min-width: 12rem"
                            dataType="numeric"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.receive') }}</span>
                            </template>
                            <template #body="{ data }">
                                $ {{ data.transaction_amount || '-'}}
                            </template>
                        </Column>

                        <Column
                            field="created_at"
                            style="min-width: 12rem"
                            dataType="date"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.requested_at') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ dayjs(data.created_at).add(8, 'hour').format('hh:mm:ss A') }}
                                </div>
                            </template>
                        </Column>

                        <Column
                                field="action"
                                header="action"
                            >
                                <template #body="{data}">
                                    <PendingWithdrawalAction
                                        :pending="data"
                                        @pendingWithdrawalActionCompleted="refreshTable"
                                    />
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
                    {{ $t('public.filter_by_date') }}
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

            <!-- Filter fund type -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    {{ $t('public.filter_by_fund_type') }}
                </div>
                <Select
                    v-model="filters['fund_type'].value"
                    :options="fundType"
                    :placeholder="$t('public.select_fund_type')"
                    class="w-full"
                    showClear
                >
                    <template #option="slotProps">
                        {{ slotProps.option.replace('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase()) }}
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