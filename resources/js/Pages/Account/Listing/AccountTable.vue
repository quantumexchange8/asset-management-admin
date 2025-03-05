<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import DatePicker from 'primevue/datepicker';
import ProgressSpinner from 'primevue/progressspinner';
import Popover from 'primevue/popover';
import Card from 'primevue/card';
import { IconXboxX, IconSearch, IconAdjustments, IconEye, IconEyeOff } from '@tabler/icons-vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import debounce from "lodash/debounce.js";
import dayjs from 'dayjs';
import { FilterMatchMode } from '@primevue/core/api';
import {generalFormat} from "@/Composables/format.js";
import { usePage } from '@inertiajs/vue3';
import EmptyData from "@/Components/EmptyData.vue";

const isLoading = ref(false);
const dt = ref(null);
const accounts = ref([]);
const {formatAmount} = generalFormat();
const totalRecords = ref(0);
const accountCounts = ref();
const totalBrokerCapital = ref();
const first = ref(0);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_request_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_request_date: { value: null, matchMode: FilterMatchMode.EQUALS },
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

            const url = route('broker_accounts.getAccountListingData', params);
            const response = await fetch(url);
            const results = await response.json();

            accounts.value = results?.data?.data;
            accountCounts.value = results?.accountCounts;
            totalBrokerCapital.value = results?.totalBrokerCapital;
            totalRecords.value = results?.data?.total;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        accounts.value = [];
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

const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
}

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
);

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

const selectedDate = ref([]);

const clearJoinDate = () => {
    selectedDate.value = [];
}

watch(selectedDate, (newDateRange) => {
    if (Array.isArray(newDateRange)) {
        const [startDate, endDate] = newDateRange;
        filters.value['start_request_date'].value = startDate;
        filters.value['end_request_date'].value = endDate;

        if (startDate !== null && endDate !== null) {
            loadLazyData();
        }
    } else {
        console.warn('Invalid date range format:', newDateRange);
    }
})

const emit = defineEmits(['update-totals']);

// Emit the totals whenever they change
watch([accountCounts, totalBrokerCapital], () => {
    emit('update-totals', {
        accountCounts: accountCounts.value,
        totalBrokerCapital: totalBrokerCapital.value,
    });
});

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        start_request_date: { value: null, matchMode: FilterMatchMode.EQUALS },
        end_request_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    };

    selectedDate.value = [];
    lazyParams.value.filters = filters.value ;
};

const getSeverity = (status) => {
    switch (status) {
        case 'linked':
            return 'success';

        case 'unlink':
            return 'danger';
    }
}

//hide password
const showPassword = ref({});

const togglePassword = (id) => {
    showPassword.value[id] = !showPassword.value[id]
}

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
                    :value="accounts"
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
                    :globalFilterFields="['name', 'email']"
                >
                    <template #header>
                        <div class="flex flex-wrap justify-between items-center">
                            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                                
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
                        <div v-if="accountCounts === 0">
                            <EmptyData
                                :title="$t('public.no_accounts')"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $t('public.account_loading_caption') }}</span>
                        </div>
                    </template>

                    <template v-if="accounts?.length > 0">
                        <Column
                            field="created_at"
                            :header="$t('public.request_date')"
                            style="min-width: 11rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #body="{ data }">
                                {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ dayjs(data.created_at).add(8, 'hour').format('hh:mm:ss A') }}
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="user_id"
                            :header="$t('public.name')"
                            style="min-width: 10rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-surface-950 dark:text-white">{{ data.user.name }}</span>
                                    <span class="text-surface-500 text-xs">{{ data.user.email }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="broker"
                            :header="$t('public.broker')"
                            style="min-width: 10rem"
                            class="hidden md:table-cell"
                        >
                            <template #body="{ data }">
                                <div class="flex gap-2 items-center">
                                    <img :src="data.broker.media[0].original_url" alt="broker_image" class="w-6 h-6 grow-0 shrink-0 rounded-full object-contain border border-surface-100 dark:border-surface-800">
                                    <div class="flex flex-col">
                                        <span class="text-surface-950 dark:text-white font-medium">{{ data.broker_login }}</span>
                                        <span class="text-surface-500">{{ data.broker.name }}</span>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="broker_capital"
                            :header="$t('public.capital_fund')"
                            style="min-width: 12rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #body="{ data }">
                                {{ formatAmount(data.broker_capital)}}
                            </template>
                        </Column>

                        <Column
                            field="master_password"
                            :header="$t('public.master_password')"
                            style="min-width: 14rem"
                            class="hidden md:table-cell"
                        >
                            <template #body="{ data }">
                                <div v-if="data.status === 'linked'" class="flex items-center gap-2">
                                    <span class="mt-1">
                                        {{ showPassword[data.id] ? data.decrypted_master_password : '*****' }}
                                    </span>
                                    <IconEye
                                        v-if="!showPassword[data.id]"
                                        size="20" stroke-width="1.5"
                                        class="cursor-pointer text-gray-500"
                                        @click="togglePassword(data.id)"
                                    />
                                    <IconEyeOff
                                        v-else
                                        size="20" stroke-width="1.5"
                                        class="cursor-pointer text-gray-500"
                                        @click="togglePassword(data.id)"
                                    />
                                </div>
                                <div v-else>
                                    -
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="status"
                            :header="$t('public.status')"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #body="{ data }">
                                <Tag
                                    :value="$t(`public.${data.status}`)"
                                    :severity="getSeverity(data.status)"
                                />
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
                                                {{ data.user.name }}
                                            </div>
                                            <img :src="data.broker.media[0].original_url" alt="broker_image" class="w-6 h-6 grow-0 shrink-0 rounded-full object-contain border border-surface-100 dark:border-surface-800">
                                            <Tag
                                                :value="$t(`public.${data.status}`)"
                                                :severity="getSeverity(data.status)"
                                            />
                                        </div>
                                        <div class="flex gap-1 items-center text-surface-500 text-xs">
                                            {{ data.user.email }}
                                            <span>|</span>
                                            <span>{{ data.broker.name }}</span>
                                            <span>|</span>
                                            <span>{{ data.broker_login }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-start">
                                        <div class="flex items-center gap-1 justify-end w-full">
                                            <div v-if="data.status === 'linked'" class="flex items-center gap-2">
                                                <span class="mt-1">
                                                    {{ showPassword[data.id] ? data.decrypted_master_password : '*****' }}
                                                </span>
                                                <IconEye
                                                    v-if="!showPassword[data.id]"
                                                    size="20" stroke-width="1.5"
                                                    class="cursor-pointer text-gray-500"
                                                    @click="togglePassword(data.id)"
                                                />
                                                <IconEyeOff
                                                    v-else
                                                    size="20" stroke-width="1.5"
                                                    class="cursor-pointer text-gray-500"
                                                    @click="togglePassword(data.id)"
                                                />
                                            </div>
                                            <div v-else>
                                                -
                                            </div>
                                           
                                        </div>

                                        <div class="flex justify-end w-full">
                                            <div class="text-xs">${{ data.broker_capital }}</div>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </Column>
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <!-- Filter Join Date-->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-surface-950 dark:text-white font-semibold">
                    {{ $t('public.filter_by_date') }}
                </div>
                <div class="relative w-full">
                    <DatePicker
                        v-model="selectedDate"
                        dateFormat="dd/mm/yy"
                        class="w-full"
                        selectionMode="range"
                        placeholder="dd/mm/yyyy - dd/mm/yyyy"
                    />
                    <div
                        v-if="selectedDate && selectedDate.length > 0"
                        class="absolute top-2/4 -mt-1.5 right-2 text-surface-400 select-none cursor-pointer bg-transparent"
                        @click="clearJoinDate"
                    >
                        <IconXboxX size="12" stoke-width="1.5" />
                    </div>
                </div>
            </div>
            <Button
                type="button"
                severity="info"
                class="w-full"
                outlined
                @click="clearFilter"
            >
            {{ $t('public.clear_all') }}
            </Button>
        </div>
    </Popover>
</template>
