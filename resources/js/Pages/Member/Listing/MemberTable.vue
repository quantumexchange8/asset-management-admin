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
import { IconXboxX, IconX, IconSearch, IconAdjustments } from '@tabler/icons-vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import debounce from "lodash/debounce.js";
import dayjs from 'dayjs';
import MemberTableAction from './MemberTableAction.vue';
import { FilterMatchMode } from '@primevue/core/api';
import { usePage } from '@inertiajs/vue3';
import EmptyData from "@/Components/EmptyData.vue";
import { useLangObserver } from '@/Composables/localeObserver';

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const users = ref([]);
const totalRecords = ref(0);
const verifiedUser = ref();
const unverifiedUser = ref();
const memberCounts = ref();
const {locale} = useLangObserver();

//filteration type and methods
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    referrer: { value: null, matchMode: FilterMatchMode.EQUALS },
    start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    country: { value: null, matchMode: FilterMatchMode.EQUALS },
    rank: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
});

//get user data
const lazyParams = ref({}); //track table parameters that need to be send to backend

const loadLazyData = (event) => { // event will retrieve from the datatable attribute
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value }; //...lazyParams.value(retain existing properties after update);
    lazyParams.value.filters = filters.value;
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
            const url = route('member.getMemberData', params);
            const response = await fetch(url);

            //BE send back result back to FE
            const results = await response.json();
            users.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            memberCounts.value = results?.memberCounts;
            verifiedUser.value = results?.verifiedUser;
            unverifiedUser.value = results?.unverifiedUser;
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

const emit = defineEmits(['updateTotalUser']);

// Emit the totals whenever they change
watch([totalRecords, verifiedUser, unverifiedUser], () => {
    emit('updateTotalUser', {
        totalRecords: totalRecords.value,
        verifiedUser: verifiedUser.value,
        unverifiedUser: unverifiedUser.value,
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

//referrer filter
const upline = ref();
const loadingUpline = ref(false);

const getUpline = async () => {
    loadingUpline.value = true;
    try {
        const response = await axios.get('/get_uplines');
        upline.value = response.data.uplines;
        console.log(response.data.uplines);
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingUpline.value = false;
    }
}

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
    getUpline();
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
watch([filters.value['country'], filters.value['rank'], filters.value['status'], filters.value['referrer']], () => {
    loadLazyData();
});

//clear all selected filter
const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['start_date'].value = null;
    filters.value['end_date'].value = null;
    filters.value['country'].value = null;
    filters.value['rank'].value = null;
    filters.value['status'].value = null;
    filters.value['referrer'].value = null;
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
                                    class="w-full md:w-28 flex items-center gap-2"
                                    outlined
                                    @click="toggle"
                                    size="small"
                                >
                                    <IconAdjustments :size="16" stroke-width="1.5" />
                                    {{ $t('public.filter') }}
                                </Button>
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div v-if="memberCounts === 0">
                            <EmptyData
                            :title="$t('public.no_members_founded')"
                            :message="$t('public.add_members_to_proceed')"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $t('public.member_loading_caption') }}</span>
                        </div>
                    </template>

                    <template v-if="users?.length > 0">
                        <Column
                            field="created_at"
                            style="min-width: 9rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.join_date') }}</span>
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
                                <span class="block">{{ $t('public.name') }}</span>
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
                                <span class="block">{{ $t('public.email') }}</span>
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
                                <span class="block">{{ $t('public.referrer') }}</span>
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
                                <span class="block">{{ $t('public.rank') }}</span>
                            </template>
                            <template #body="{ data }">
                                <Tag
                                    severity="secondary"
                                    :value="data.rank.rank_name === 'member' ? $t(`public.${data.rank.rank_name}`) : data.rank.rank_name"
                                />
                            </template>
                        </Column>

                        <Column
                            field="role"
                            style="min-width: 10rem"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.role') }}</span>
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
                                <span class="block">{{ $t('public.country') }}</span>
                            </template>
                            <template #body="{data}">
                                <div class="flex items-center gap-1">
                                    <img
                                        v-if="data.country.iso2"
                                        :src="`https://flagcdn.com/w40/${data.country.iso2.toLowerCase()}.png`"
                                        :alt="data.country.iso2"
                                        width="18"
                                        height="12"
                                    />
                                    <div class="max-w-[200px] truncate">{{ JSON.parse(data.country.translations)[locale] || data.country.name }}</div>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="kyc_status"
                            sortable
                            style="min-width: 6rem;"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.status') }}</span>
                            </template>
                            <template #body="{ data }">
                                <Tag :value="$t(`public.${data.kyc_status}`)" :severity="getSeverity(data.kyc_status)" />
                            </template>
                        </Column>

                        <Column
                            field="action"
                            frozen
                            alignFrozen="right"
                            header=""
                            style="width: 5%"
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
            <!-- Filter Referrer -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    {{ $t('public.filter_by_referrer') }}
                </div>
                <Select
                    v-model="filters['referrer'].value"
                    :options="upline"
                    optionLabel="name"
                    :placeholder="$t('public.select_referrer')"
                    filter
                    :filter-fields="['name']"
                    :loading="loadingUpline"
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

            <!-- Filter Country -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    {{ $t('public.filter_by_country') }}
                </div>
                <Select
                    v-model="filters['country'].value"
                    :options="countries"
                    optionLabel="name"
                    :placeholder="$t('public.select_country')"
                    filter
                    :filter-fields="['name', 'iso2']"
                    :loading="loadingCountries"
                    class="w-full"
                    showClear
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="flex items-center">
                            <div class="leading-tight">{{ JSON.parse(slotProps.value.translations)[locale] || slotProps.value.name }}</div>
                        </div>
                        <span v-else>{{ slotProps.placeholder }}</span>
                    </template>
                    <template #option="slotProps">
                        <div class="flex items-center gap-1">
                            <img
                                v-if="slotProps.option.iso2"
                                :src="`https://flagcdn.com/w40/${slotProps.option.iso2.toLowerCase()}.png`"
                                :alt="slotProps.option.iso2"
                                width="18"
                                height="12"
                            />
                            <div class="max-w-[200px] truncate">{{ JSON.parse(slotProps.option.translations)[locale] || slotProps.option.name }}</div>
                        </div>
                    </template>
                </Select>
            </div>

            <!-- Filter Rank -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    {{ $t('public.filter_by_rank') }}
                </div>
                <Select
                    v-model="filters['rank'].value"
                    :options="ranks"
                    optionLabel="rank_name"
                    :placeholder="$t('public.select_rank')"
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
                    {{ $t('public.filter_by_status') }}
                </div>
                <Select
                    v-model="filters['status'].value"
                    :options="kycStatus"
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
