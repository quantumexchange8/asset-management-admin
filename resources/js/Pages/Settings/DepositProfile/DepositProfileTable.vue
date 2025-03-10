<script setup>
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import Select from 'primevue/select';
import Popover from 'primevue/popover';
import ProgressSpinner from 'primevue/progressspinner';
import EmptyData from '@/Components/EmptyData.vue';
import { IconXboxX, IconX, IconSearch, IconAdjustments } from '@tabler/icons-vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { useLangObserver } from '@/Composables/localeObserver';
import debounce from "lodash/debounce.js";
import { usePage } from '@inertiajs/vue3';
import ToggleDepositProfileStatus from './ToggleDepositProfileStatus.vue';
import Tag from "primevue/tag";
import DepositProfileAction from './DepositProfileAction.vue';

const props = defineProps({
    depositProfileCounts: Number,
});

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const depositProfile = ref([]);
const totalRecords = ref(0);
const depositProfileCounts = ref();
const { locale } = useLangObserver();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    type: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const lazyParams = ref({});

const loadLazyData = (event) => {
    isLoading.value = true;
    lazyParams.value = {
        ...lazyParams.value,
        first: event?.first || first.value
    };
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
            const url = route('settings.getDepositProfileData', params);
            const response = await fetch(url);

            //BE send back result back to FE
            const results = await response.json();
            depositProfile.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            depositProfileCounts.value= results?.depositProfileCounts;
            isLoading.value = false;
        }, 100);
    } catch (e) {
        depositProfile.value = [];
        totalRecords.value = 0;
        isLoading.value = false;
    }
}

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

//type filter
const type = ref(['bank', 'crypto']);

//filter toggle
const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
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

watch([filters.value['type']], () => {
    loadLazyData();
});

const clearAll = () => {
    filters.value['global'].value = null;
    filters.value['type'].value = null;
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
    <Card>
        <template #content>
            <div class="w-full">
                <DataTable
                    :value="depositProfile"
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
                    :globalFilterFields="['name']"
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
                                    <IconAdjustments :size="15"/>
                                    {{ $t('public.filter') }}
                                </Button>
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div v-if="depositProfileCounts === 0">
                            <EmptyData
                                :title="$t('public.no_deposit_profile')"
                                :message="$t('public.add_deposit_profile_to_proceed')"
                            />
                        </div>
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <ProgressSpinner
                                strokeWidth="4"
                            />
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ $t('public.deposit_profile_loading_caption') }}</span>
                        </div>
                    </template>

                    <template v-if="depositProfile?.length > 0">
                        <Column
                            field="name"
                            style="min-width: 8rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.name') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.name }}
                            </template>
                        </Column>

                        <Column
                            field="type"
                            style="min-width: 8rem"
                            class="hidden md:table-cell"
                            sortable
                        >
                            <template #header>
                                <span class="block">{{ $t('public.type') }}</span>
                            </template>
                            <template #body="{ data }">
                                <Tag
                                    :severity="data.type === 'crypto' ? 'info' : 'secondary'"
                                    :value="$t(`public.${data.type}`)"
                                />
                            </template>
                        </Column>

                        <Column
                            field="asset"
                            style="min-width: 8rem"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.asset') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.type === 'bank' ? data.bank_name : data.crypto_tether }}
                            </template>
                        </Column>

                        <Column
                            field="account_number"
                            style="min-width: 12rem"
                            class="hidden md:table-cell"
                        >
                            <template #header>
                                <span class="block">{{ $t('public.account_number') }}</span>
                            </template>
                            <template #body="{ data }">
                                {{ data.account_number }}
                            </template>
                        </Column>

                        <!-- mobile view -->
                        <Column field="mobile" class="table-cell md:hidden">
                            <template #body="{ data }">
                                <div class="flex items-center gap-3 justify-between w-full">
                                    <div class="flex flex-col items-start">
                                        <div class="font-medium max-w-[180px] truncate">
                                            {{ data.name }}
                                        </div>
                                        <Tag
                                            :severity="data.type === 'crypto' ? 'info' : 'secondary'"
                                            :value="$t(`public.${data.type}`)"
                                        />
                                    </div>

                                    <div class="flex flex-col items-start pr-2">
                                        <div class="flex justify-end text-base w-full">
                                            <span class="break-words max-w-40 text-surface-950 dark:text-white">{{ data.account_number }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="action"
                            style="width: 5%"
                        >
                            <template #body="{ data }">
                                <DepositProfileAction 
                                    :depositProfile="data"
                                />
                            </template>
                        </Column>

<!--                        <Column-->
<!--                            field="status"-->
<!--                            style="min-width: 12rem"-->
<!--                            sortable-->
<!--                        >-->
<!--                            <template #header>-->
<!--                                <span class="block">{{ $t('public.status') }}</span>-->
<!--                            </template>-->
<!--                            <template #body="{ data }">-->
<!--                                <ToggleDepositProfileStatus -->
<!--                                    :depositProfile="data"-->
<!--                                />-->
<!--                            </template>-->
<!--                        </Column>-->

                    </template>
                </DataTable>
            </div>
        </template>
    </Card>

    <Popover ref="op">
        <div class="flex flex-col gap-6 w-60">
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-sm text-surface-ground dark:text-white">
                    {{ $t('public.filter_by_type') }}
                </div>
                <Select
                    v-model="filters['type'].value"
                    :options="type"
                    :placeholder="$t('public.select_type')"
                    class="w-full"
                    showClear
                >
                    <template #option="slotProps">
                        {{ $t(`public.${slotProps.option}`)}}
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
