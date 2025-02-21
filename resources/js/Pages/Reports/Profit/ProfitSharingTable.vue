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
import {useLangObserver} from "@/Composables/localeObserver.js";
import { result } from "lodash";
import EmptyData from "@/Components/EmptyData.vue";

const exportStatus = ref(false);
const isLoading = ref(false);
const dt = ref(null);
const connections = ref([]);
const {formatAmount} = generalFormat();
const totalRecords = ref(0);
const first = ref(0);
const totalBonusAmount = ref();
const maxBonusAmount = ref();
const profitSharingCounts = ref();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    start_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
    end_join_date: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const lazyParams = ref({});
const {locale} = useLangObserver();

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

            const url = route('report.getProfitSharingData', params);
            const response = await fetch(url);
            const results = await response.json();

            connections.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            totalBonusAmount.value = results?.totalBonusAmount;
            maxBonusAmount.value = results?.maxBonusAmount;
            profitSharingCounts.value = results?.profitSharingCounts;
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
watch([totalBonusAmount, maxBonusAmount], () => {
    emit('update-totals', {
        totalBonusAmount: totalBonusAmount.value,
        maxBonusAmount: maxBonusAmount.value,
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
                    :currentPageReportTemplate="$t('public.paginator_caption')"
                    v-model:filters="filters"
                    ref="dt"
                    dataKey="id"
                    :loading="isLoading"
                    :totalRecords="totalRecords"
                    @page="onPage($event)"
                    @sort="onSort($event)"
                    @filter="onFilter($event)"
                    :globalFilterFields="['user.name', 'user.email', 'subject_user.name', 'subject_user.email', 'broker.name']"
                >
                    <template #header>
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
                    </template>

                    <template #empty>
                        <div v-if="profitSharingCounts === 0">
                            <EmptyData
                                :title="$t('public.no_data')"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $t('public.profit_sharing_loading_caption') }}</span>
                        </div>
                    </template>

                    <template v-if="connections?.length > 0">
                        <Column
                            field="created_at"
                            :header="$t('public.date')"
                            class="hidden md:table-cell min-w-32"
                            sortable
                        >
                            <template #body="{ data }">
                                {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                            </template>
                        </Column>

                        <Column
                            field="client"
                            :header="$t('public.client')"
                            class="hidden md:table-cell min-w-32"
                        >
                            <template #body="{ data }">
                                <span class="text-surface-950 dark:text-white">{{ data.subject_user.username }}</span>
                            </template>
                        </Column>

                        <Column
                            field="broker"
                            :header="$t('public.broker')"
                            class="hidden md:table-cell min-w-32"
                        >
                            <template #body="{ data }">
                                <div class="flex gap-2 items-center">
                                    <img :src="data.broker.media[0].original_url" alt="broker_image" class="w-6 h-6 grow-0 shrink-0 rounded-full object-contain border border-surface-100 dark:border-surface-800">
                                    <div class="flex flex-col">
                                        <span class="text-surface-950 dark:text-white font-medium">{{ data.user_broker_login }}</span>
                                        <span class="text-surface-500">{{ data.broker.name }}</span>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="bonus_type"
                            :header="$t('public.description')"
                            class="hidden md:table-cell"
                            :class="locale === 'cn' ? 'min-w-24' : 'min-w-40'"
                        >
                            <template #body="{ data }">
                                <Tag
                                    severity="secondary"
                                    :value="$t(`public.${data.bonus_type}`)"
                                />
                            </template>
                        </Column>

                        <Column
                            field="close_time"
                            :header="$t('public.close_time')"
                            class="min-w-32 hidden md:table-cell"
                        >
                            <template #body="{data}">
                                <div class="flex flex-col">
                                    <span class="text-surface-950 dark:text-white font-medium">{{ dayjs(data.close_time).format('YYYY-MM-DD') }}</span>
                                    <span class="text-surface-500 text-xs">{{ dayjs(data.close_time).format('hh:mm:ss A') }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="symbol"
                            :header="$t('public.symbol')"
                            class="hidden md:table-cell"
                            style="min-width: 7rem"
                        >
                            <template #body="{data}">
                                <span class="font-medium">{{ data.symbol }}</span>
                            </template>
                        </Column>

                        <Column
                            field="net_profit"
                            :header="`${$t('public.net_profit')} ($)`"
                            class="hidden md:table-cell"
                            :class="locale === 'cn' ? 'min-w-24' : 'min-w-36'"
                        >
                            <template #body="{data}">
                                {{ formatAmount(data.net_profit) }}
                            </template>
                        </Column>

                        <Column
                            field="distribute_amount"
                            :header="`${$t('public.distribute_amount')} ($)`"
                            sortable
                            class="hidden md:table-cell"
                            :class="locale === 'cn' ? 'min-w-36' : 'min-w-44'"
                        >
                            <template #body="{data}">
                                {{ formatAmount(data.distribute_amount, 4) }}
                            </template>
                        </Column>

                        <Column
                            field="remaining_percentage"
                            :header="`${$t('public.allocated')} (%)`"
                            sortable
                            class="hidden md:table-cell"
                            :class="locale === 'cn' ? 'min-w-40' : 'min-w-44'"
                        >
                            <template #body="{data}">
                                {{ formatAmount(data.remaining_percentage) }}
                            </template>
                        </Column>

                        <Column
                            field="bonus_amount"
                            :header="`${$t('public.amount')} ($)`"
                            sortable
                            frozen
                            align-frozen="right"
                            class="hidden md:table-cell"
                            :class="locale === 'cn' ? 'min-w-36' : 'min-w-40'"
                        >
                            <template #body="{ data }">
                                <span class="font-semibold">{{ formatAmount(data.bonus_amount, 4) }}</span>
                            </template>
                        </Column>

                        <!-- Mobile view -->
                        <Column
                            field="mobile"
                            class="md:hidden"
                        >
                            <template #body="{ data }">
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col items-start">
                                        <div class="font-semibold">
                                            {{ $t(`public.${data.bonus_type}`) }}
                                        </div>
                                        <div class="flex gap-1 items-center text-surface-500 text-xs">
                                            {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                                            <span>|</span>
                                            <span>${{ formatAmount(data.net_profit) }}</span>
                                            <span>|</span>
                                            <span>{{ data.remaining_percentage }}%</span>
                                        </div>
                                    </div>
                                    <div class="text-base font-semibold">
                                        ${{ formatAmount(data.bonus_amount, 4) }}
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
