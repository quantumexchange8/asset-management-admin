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
import { usePage } from '@inertiajs/vue3';
import Import from './Import.vue';
import EmptyData from '@/Components/EmptyData.vue';
import DepositHistoryAction from './DepositHistoryAction.vue';
import { generalFormat } from '@/Composables/format';

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const depositHistory = ref([]);
const totalRecords = ref(0);
const depositHistoryCounts = ref();
const successAmount = ref();
const rejectAmount = ref();
const {formatAmount} = generalFormat();
const exportStatus = ref(false);

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
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
            const url = route('transaction.history.getDepositHistoryData', params);
            const response = await fetch(url);

            //BE send back result back to FE
            const results = await response.json();
            depositHistory.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            depositHistoryCounts.value = results?.depositHistoryCounts;
            successAmount.value = results?.successAmount;
            rejectAmount.value = results?.rejectAmount;
            isLoading.value = false;
        }, 100);
    } catch (e) {
        depositHistory.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};

// get deposit filter,paginate,sorting input and pass to the event of lazyParams into each const then to loadLazyData
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

const emit = defineEmits(['updateDepositHistory']);

// Emit the totals whenever they change
watch([successAmount, rejectAmount], () => {
    emit('updateDepositHistory', {
        successAmount: successAmount.value,
        rejectAmount: rejectAmount.value,
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

//filter status
const status = ref(['success','rejected']);

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
    loadLazyData()
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

        case 'success':
            return 'success';

        case 'rejected':
            return 'danger';
    }
};

//export button
const exportTable = () => {
    exportStatus.value = true;
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value };
    lazyParams.value.filters = filters.value;

    if (filters.value) {
        lazyParams.value.filters = { ...filters.value };
    } else {
        lazyParams.value.filters = {};
    }

    let params = {
        include: [],
        lazyEvent: JSON.stringify(lazyParams.value),
        exportStatus: true,
    };

    const url = route('transaction.history.getDepositHistoryData', params);

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
    <Card class="w-full">
        <template #content>
            <div class="w-full">
                <DataTable
                    :value="depositHistory"
                    lazy
                    paginator
                    removableSort
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    :first="first"
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    :currentPageReportTemplate="$t('public.paginator_caption')"
                    v-model:filters="filters"
                    ref="dt"
                    dataKey="id"
                    :loading="isLoading"
                    :totalRecords="totalRecords"
                    @page="onPage($event)"
                    @sort="onSort($event)"
                    @filter="onFilter($event)"
                    :globalFilterFields="['name' ,'transaction_number']"
                >
                    <template #header>
                        <div class="flex flex-wrap justify-between items-center">
                            <div class="flex items-center space-x-4 w-full md:w-auto">

                                <!-- Search bar -->
                                <IconField class="w-full">
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
                                        class="absolute top-1/2 -translate-y-1/2 right-4 text-surface-300 hover:text-surface-400 select-none cursor-pointer"
                                        @click="clearFilterGlobal"
                                    >
                                        <IconXboxX aria-hidden="true" :size="15" />
                                    </div>
                                </IconField>

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
                            </div>

                            <div class="flex items-center space-x-4 w-full md:w-auto mt-4 md:mt-0">
                                <!-- Export button -->
                                <Button
                                    type="button"
                                    severity="info"
                                    class="w-full md:w-auto"
                                    @click="exportTable"
                                    :disabled="exportStatus==='true'"
                                >
                                    <span class="pr-1">{{ $t('public.export') }}</span>
                                    <IconDownload size="16" stroke-width="1.5"/>
                                </Button>
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div v-if="depositHistoryCounts === 0">
                            <EmptyData
                                :title="$t('public.no_deposit_founded')"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span v-if="exportStatus === 'true'" class="text-sm text-surface-700 dark:text-surface-300">{{ $t('public.export_caption') }}</span>
                            <span v-else class="text-sm text-surface-700 dark:text-surface-300">{{ $t('public.deposit_loading_caption') }}</span>
                        </div>
                    </template>

                    <template v-if="depositHistory?.length > 0">
                        <Column
                            field="approval_at"
                            style="min-width: 11rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.approved_at') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ dayjs(data.approval_at).format('YYYY-MM-DD') }}
                                <div class="text-xs text-surface-500 mt-1">
                                    {{ dayjs(data.approval_at).add(8, 'hour').format('hh:mm:ss A') }}
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="user_id"
                            style="min-width: 13rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.name') }}</span>
                            </template>
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-surface-950 dark:text-white">{{ data.user.name }}</span>
                                    <span class="text-surface-500 text-xs">{{ data.user.email }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="user.upline"
                            style="min-width: 12rem"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.upline') }}</span>
                            </template>
                            <template #body="{ data }">
                                <div
                                    v-if="data.user.upline_id"
                                    class="flex flex-col"
                                >
                                    <span class="text-surface-950 dark:text-white">{{ data.user.upline.name }}</span>
                                    <span class="text-surface-500">{{ data.user.upline.email }}</span>
                                </div>
                                <div v-else>
                                    -
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="transaction_number"
                            style="min-width: 16rem"
                            class="hidden md:table-cell"
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
                            field="to_wallet_id"
                            :header="$t('public.wallet')"
                            style="min-width: 8rem"
                            class="hidden md:table-cell"
                        >
                            <template #body="{ data }">
                                {{ $t(`public.${data.to_wallet?.type}`) || '-'}}
                            </template>
                        </Column>

                        <Column
                            field="amount"
                            style="min-width: 9rem"
                            class="hidden md:table-cell"
                            dataType="numeric"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.amount') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ formatAmount(data.amount) }}
                            </template>
                        </Column>

                        <Column
                            field="status"
                            style="min-width: 8rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.status') }}</span>
                            </template>
                            <template #body="{ data }">
                                <Tag :value="$t(`public.${data.status}`)" :severity="getSeverity(data.status)" />
                            </template>
                        </Column>

                        <!-- mobile view -->
                        <Column
                            field="mobile"
                            class="table-cell md:hidden"
                        >
                            <template #body={data}>
                                <div class="flex items-center gap-3 justify-between w-full">
                                    <div class="flex flex-col items-start">
                                        <div class="flex items-center gap-1">
                                            <div class="font-medium max-w-[180px] truncate">
                                                {{ data.user.name }}
                                            </div>
                                            <span class="text-xs text-surface-400 dark:text-surface-500 truncate">
                                                <Tag :value="$t(`public.${data.status}`)" :severity="getSeverity(data.status)" />
                                            </span>
                                        </div>
                                        <div class="flex gap-1 items-center text-surface-500 text-xs">
                                            <span>{{ dayjs(data.created_at).format('YYYY-MM-DD') }}</span>
                                            <span>|</span>
                                            <span class="font-bold dark:text-white/60">{{ data.transaction_number }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-start">
                                        <div class="flex items-center gap-1 justify-end w-full">
                                            {{ $t(`public.${data.to_wallet?.type}`) || '-'}}
                                        </div>

                                        <div class="flex justify-end w-full">
                                            {{ formatAmount(data.amount) }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="action"
                            alignFrozen="right"
                            frozen
                            style="width: 5%;"
                        >
                            <template #body="{ data }">
                                <DepositHistoryAction
                                    :depositHistory="data"
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
                <div class="flex self-stretch text-sm text-surface-950 dark:text-white">
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
                        class="absolute top-2/4 -mt-2 right-2 text-surface-400 select-none cursor-pointer bg-transparent"
                        @click="clearDate"
                    >
                        <IconX :size="15" strokeWidth="1.5"/>
                    </div>
                </div>
            </div>

            <!-- Filter status -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-950 dark:text-white">
                    {{ $t('public.filter_by_status') }}
                </div>
                <Select
                    v-model="filters['status'].value"
                    :options="status"
                    :placeholder="$t('public.select_status')"
                    class="w-full"
                    showClear
                >
                    <template #option="slotProps">
                        <Tag :value="$t(`public.${slotProps.option}`)" :severity="getSeverity(slotProps.option)" />
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
