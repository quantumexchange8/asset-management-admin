<script setup>
import Card from "primevue/card";
import dayjs from "dayjs";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import {
    IconAdjustments,
    IconSearch,
    IconXboxX,
    IconDownload,
} from "@tabler/icons-vue";
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import {onMounted, ref, watch, watchEffect} from "vue";
import debounce from "lodash/debounce.js";
import { FilterMatchMode } from '@primevue/core/api';
import {generalFormat} from "@/Composables/format.js";
import {usePage} from "@inertiajs/vue3";
import Tag from "primevue/tag";
import Popover from "primevue/popover";
import DatePicker from "primevue/datepicker";
import EmptyData from "@/Components/EmptyData.vue";

const exportStatus = ref(false);
const isLoading = ref(false);
const dt = ref(null);
const connections = ref([]);
const {formatAmount} = generalFormat();
const totalRecords = ref(0);
const first = ref(0);
const totalActiveFund = ref();
const totalConnections = ref();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
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

            const url = route('connection.getConnections', params);
            const response = await fetch(url);
            const results = await response.json();

            connections.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            totalActiveFund.value = results?.totalActiveFund;
            totalConnections.value = results?.totalConnections;
            isLoading.value = false;

        }, 100);
    }  catch (e) {
        connections.value = [];
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
        filters.value['start_join_date'].value = startDate;
        filters.value['end_join_date'].value = endDate;

        if (startDate !== null && endDate !== null) {
            loadLazyData();
        }
    } else {
        console.warn('Invalid date range format:', newDateRange);
    }
})

const emit = defineEmits(['update-totals']);

// Emit the totals whenever they change
watch([totalActiveFund, totalConnections], () => {
    emit('update-totals', {
        totalActiveFund: totalActiveFund.value,
        totalConnections: totalConnections.value,
    });
});

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        start_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
        end_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    };

    selectedDate.value = [];
    lazyParams.value.filters = filters.value ;
};

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

    const url = route('connection.getConnections', params);

    try {

        window.location.href = url;
    } catch (e) {
        console.error('Error occurred during export:', e);
    } finally {
        isLoading.value = false;
        exportStatus.value = false;
    }
};

const getSeverity = (status) => {
    switch (status) {
        case 'withdrawal':
            return 'danger';

        case 'initial_join':
            return 'success';

        case 'top_up':
            return 'info';
    }
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
                    :value="connections"
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
                    :globalFilterFields="['name', 'email', 'connection_number']"
                >
                    <template #header>
                        <div class="flex flex-wrap justify-between items-center">
                            <div class="flex gap-3 w-full md:w-auto">
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

                            <div class="flex items-center space-x-4 w-full md:w-auto mt-4 md:mt-0">
                                <!-- Export button -->
                                <Button
                                    type="button"
                                    severity="info"
                                    class="w-full md:w-auto"
                                    @click="exportTable"
                                    :disabled="exportStatus"
                                >
                                    <span class="pr-1">{{ $t('public.export') }}</span>
                                    <IconDownload size="16" stroke-width="1.5"/>
                                </Button>
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div v-if="totalConnections === 0">
                            <EmptyData
                                :title="$t('public.no_connection')"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ $t('public.connection_loading_caption') }}</span>
                        </div>
                    </template>

                    <template v-if="connections?.length > 0">
                        <Column
                            field="date"
                            :header="$t('public.date')"
                            style="min-width: 8rem"
                            class="hidden md:table-cell"
                        >
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-surface-950 dark:text-white">{{ dayjs(data.joined_at ?? data.removed_at).format('YYYY-MM-DD') }}</span>
                                    <span class="text-surface-500 text-xs">  {{ dayjs(data.joined_at ?? data.removed_at).format('hh:mm:ss A') }}</span>
                                </div>
                              
                            </template>
                        </Column>

                        <Column
                            field="user_id"
                            :header="$t('public.name')"
                            style="min-width: 8rem"
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
                            style="min-width: 8rem"
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
                            field="connection_number"
                            :header="$t('public.connection_number')"
                            style="min-width: 15rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #body="{ data }">
                                {{ data.connection_number }}
                            </template>
                        </Column>

                        <Column
                            field="capital_fund"
                            :header="$t('public.fund')"
                            style="min-width: 8rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #body="{ data }">
                                ${{ formatAmount(data.capital_fund) }}
                            </template>
                        </Column>

                        <Column
                            field="connection_type"
                            :header="$t('public.type')"
                            style="min-width: 7rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #body="{ data }">
                                <Tag
                                    :value="$t(`public.${data.connection_type}`)"
                                    :severity="getSeverity(data.connection_type)"
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
                                            <div class="flex gap-2 items-center">

                                                <img 
                                                    :src="data.broker.media[0].original_url" 
                                                    alt="broker_image" 
                                                    class="w-6 h-6 grow-0 shrink-0 rounded-full object-contain border border-surface-100 dark:border-surface-800"
                                                >

                                                <div class="flex flex-col">
                                                    <div class="flex items-center gap-2">
                                                        <div class="font-medium max-w-[180px] truncate">
                                                            {{ data.user.name }}
                                                        </div>
                                                        <span class="text-xs text-surface-400 dark:text-surface-500 truncate">
                                                            {{ dayjs(data.joined_at ?? data.removed_at).format('YYYY-MM-DD') }}
                                                        </span>
                                                    </div>

                                                    <div class="flex gap-1 text-surface-400 dark:text-surface-500 text-xs max-w-[220px] truncate">
                                                        <span>{{ data.broker_login }}</span>
                                                        <span>|</span>
                                                        <span class="font-bold dark:text-white/60">{{ data.connection_number }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-start">
                                        <div class="flex items-center gap-1 justify-end w-full">
                                            <Tag
                                                :value="$t(`public.${data.connection_type}`)"
                                                :severity="getSeverity(data.connection_type)"
                                            />
                                        </div>

                                        <div class="flex justify-end w-full">
                                            ${{ formatAmount(data.capital_fund) }}
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
                    {{ $t('public.filter_join_date') }}
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
