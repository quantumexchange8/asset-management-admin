<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
import { FilterMatchMode } from '@primevue/core/api';
import KycAction from "@/Pages/Member/Listing/Partial/KycAction.vue";
import EmptyData from '@/Components/EmptyData.vue';
import {usePage} from "@inertiajs/vue3";
import {useLangObserver} from "@/Composables/localeObserver.js";

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const users = ref([]);
const totalRecords = ref(0);
const pendingKycCounts = ref();
const {locale} = useLangObserver();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    country: { value: null, matchMode: FilterMatchMode.EQUALS },
    rank: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;

    lazyParams.value = { ...lazyParams.value, first: event?.first || first.value };
    lazyParams.value.filters = filters.value;
    try {
        setTimeout(async () => {
            const params = {
                page: JSON.stringify(event?.page + 1),
                sortField: event?.sortField,
                sortOrder: event?.sortOrder,
                include: [],
                lazyEvent: JSON.stringify(lazyParams.value)
            };

            const url = route('member.getPendingKycData', params);
            const response = await fetch(url);
            const results = await response.json();

            users.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            pendingKycCounts.value = results?.pendingKycCounts;
            isLoading.value = false;
        }, 100);
    }  catch (e) {
        users.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
};
const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onSort = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onFilter = (event) => {
    lazyParams.value.filters = filters.value ;
    loadLazyData(event);
};

//Date Filter
const selectedDate = ref([]);

const clearDate = () => {
    selectedDate.value = [];
};

watch(selectedDate, (newDateRange) => {
    if (Array.isArray(newDateRange)) {
        const [startDate, endDate] = newDateRange;
        filters.value['start_date'].value = startDate;
        filters.value['end_date'].value = endDate;

        if (startDate !== null && endDate !== null) {
            loadLazyData();
        }
    } else {
        console.warn('Invalid date range format:', newDateRange);
    }
})

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

//filter toggle
const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
    getCountries();
    getRanks();
};

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

watch(
    filters.value['global'],
    debounce(() => {
        loadLazyData();
    }, 300)
)

watch([filters.value['country'], filters.value['rank']], () => {
    loadLazyData();
});

const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['start_date'].value = null;
    filters.value['end_date'].value = null;
    filters.value['country'].value = null;
    filters.value['rank'].value = null;
    selectedDate.value = [];
};

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
    <AuthenticatedLayout title="pending_kyc">
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
                        :currentPageReportTemplate="$t('public.paginator_caption')"
                        v-model:filters="filters"
                        ref="dt"
                        dataKey="id"
                        :loading="isLoading"
                        :totalRecords="totalRecords"
                        @page="onPage($event)"
                        @sort="onSort($event)"
                        @filter="onFilter($event)"
                        :globalFilterFields="['name', 'email', 'username', 'id_number']"
                    >
                        <template #header>
                            <div class="flex flex-wrap justify-between items-center">
                                <div class="flex flex-col md:flex-row items-center self-stretch gap-3 w-full md:w-auto">
                                    <!-- Search bar -->
                                    <IconField class="w-full md:w-auto">
                                        <InputIcon>
                                            <IconSearch :size="16" stroke-width="1.5" />
                                        </InputIcon>
                                        <InputText
                                            v-model="filters['global'].value"
                                            :placeholder="$t('public.search_keyword')"
                                            type="text"
                                            class="block font-normal w-full pl-10 pr-10"
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
                            </div>
                        </template>

                        <template #empty>
                            <div v-if="pendingKycCounts === 0">
                                <EmptyData
                                    :title="$t('public.no_members_founded')"
                                />
                            </div>
                        </template>

                        <template #loading>
                            <div class="flex flex-col gap-2 items-center justify-center">
                                <ProgressSpinner
                                    strokeWidth="4"
                                />
                                <span class="text-sm text-surface-700 dark:text-surface-300">{{ $t('public.pending_kyc_loading_caption') }}</span>
                            </div>
                        </template>

                        <template v-if="users?.length > 0">
                            <Column
                                field="kyc_requested_at"
                                :header="$t('public.date')"
                                sortable
                                style="min-width: 9rem"
                                class="hidden md:table-cell"
                            >
                                <template #body="{ data }">
                                    {{ dayjs(data.kyc_requested_at).format('YYYY-MM-DD') }}
                                    <div class="text-xs text-surface-500 mt-1">
                                        {{ dayjs(data.kyc_requested_at).add(8, 'hour').format('hh:mm:ss A') }}
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="user_id"
                                :header="$t('public.name')"
                                style="min-width: 12rem"
                                class="hidden md:table-cell"
                                sortable
                            >
                                <template #body="{ data }">
                                    <div class="flex flex-col">
                                        <span class="text-surface-950 dark:text-white">{{ data.name }}</span>
                                        <span class="text-surface-500 text-xs">{{ data.email }}</span>
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="identity_number"
                                :header="$t('public.identity_number')"
                                style="min-width: 13rem"
                                class="hidden md:table-cell"
                            >
                                <template #body="{data}">
                                    {{ data.identity_number }}
                                </template>
                            </Column>

                            <Column
                                field="upline_id"
                                :header="$t('public.referrer')"
                                style="min-width: 11rem"
                                class="hidden md:table-cell"
                            >
                                <template #body="{data}">
                                    <div
                                        v-if="data.upline_id"
                                        class="flex flex-col"
                                    >
                                        <span class="text-surface-950 dark:text-white">{{ data.upline.name }}</span>
                                        <span class="text-surface-500 text-xs">{{ data.upline.email }}</span>
                                    </div>
                                    <div v-else>
                                        -
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="setting_rank_id"
                                :header="$t('public.rank')"
                                style="min-width: 8rem"
                                class="hidden md:table-cell"
                                sortable
                            >
                                <template #body="{ data }">
                                    <Tag
                                        severity="secondary"
                                        :value="data.rank.rank_name === 'member' ? $t(`public.${data.rank.rank_name}`) : data.rank.rank_name"
                                    />
                                </template>
                            </Column>

                            <Column
                                field="country_id"
                                :header="$t('public.country')"
                                style="min-width: 10rem"
                                class="hidden md:table-cell"
                                sortable
                            >
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

                            <!-- mobile view -->
                            <Column
                                field="mobile"
                                class="table-cell md:hidden"
                            >
                                <template #body="{data}">
                                    <div class="flex items-center gap-3 justify-between w-full">
                                        <div class="flex flex-col items-start">
                                            <div class="flex items-center gap-1">
                                                <div class="font-medium max-w-[180px] truncate">
                                                    {{ data.name }}
                                                </div>
                                                <Tag
                                                    severity="secondary"
                                                    :value="data.rank.rank_name === 'member' ? $t(`public.${data.rank.rank_name}`) : data.rank.rank_name"
                                                />
                                            </div>
                                            <div class="text-surface-400 dark:text-surface-500 text-xs max-w-[220px] truncate">
                                                {{ data.email }}
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-start">
                                            <div class="flex items-center gap-1 justify-end w-full">
                                                <img
                                                    v-if="data.country.iso2"
                                                    :src="`https://flagcdn.com/w40/${data.country.iso2.toLowerCase()}.png`"
                                                    :alt="data.country.iso2"
                                                width="18"
                                                    height="12"
                                                />
                                                <div class="text-xs">{{ data.country.iso2 }}</div>
                                            </div>

                                            <div class="flex justify-end w-full">
                                                <Tag
                                                    severity="secondary"
                                                    :value="data.identity_number"
                                                    class="!text-xs"
                                                />
                                            </div>
                                       </div>
                                    </div>
                                </template>
                            </Column>

                            <Column
                                field="action"
                                :style="locale === 'en' 
                                    ? 'width: 5%'
                                    : 'width: 5%; min-width: 86px;'"
                            >
                                <template #body="{ data }">
                                    <KycAction 
                                        :pending="data"
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
                        {{ $t('public.filter_by_date' )}}
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

                <!-- Filter Country -->
                <div class="flex flex-col gap-2 items-center self-stretch">
                    <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                        {{ $t('public.filter_by_country' )}}
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
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center">
                                <div class="leading-tight">{{ JSON.parse(slotProps.value.translations)[locale] || slotProps.value.name }}</div>
                            </div>
                            <span v-else class="text-surface-400 dark:text-surface-500">{{ slotProps.placeholder }}</span>
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
                        {{ $t('public.filter_by_rank' )}}
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
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center">
                                {{ slotProps.value.rank_name === 'member' ? $t(`public.${slotProps.value.rank_name}`) : slotProps.value.rank_name }}
                            </div>
                            <span v-else>{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                            <div>{{ slotProps.option.rank_name === 'member' ? $t(`public.${slotProps.option.rank_name}`) : slotProps.option.rank_name }}</div>
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
    </AuthenticatedLayout>
</template>
