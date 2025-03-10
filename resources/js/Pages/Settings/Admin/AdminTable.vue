<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import ProgressSpinner from 'primevue/progressspinner';
import Card from 'primevue/card';
import { IconXboxX, IconSearch } from '@tabler/icons-vue';
import { onMounted, ref, watch, watchEffect } from 'vue';
import debounce from "lodash/debounce.js";
import { FilterMatchMode } from '@primevue/core/api';
import EmptyData from '@/Components/EmptyData.vue';
import {usePage} from "@inertiajs/vue3";
import {useLangObserver} from "@/Composables/localeObserver.js";
import AdminTableAction from './AdminTableAction.vue';

const props = defineProps({
    permissions: Object,
    permissionsCount: Number,
})

const isLoading = ref(false);
const dt = ref(null);
const first = ref(0);
const users = ref([]);
const totalRecords = ref(0);
const adminCounts = ref();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
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

            const url = route('settings.getAdminListingData', params);
            const response = await fetch(url);
            const results = await response.json();

            users.value = results?.data?.data;
            totalRecords.value = results?.data?.total;
            adminCounts.value = results?.adminCounts;
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
    <Card class="w-full">
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
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <div v-if="adminCounts === 0">
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
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ $t('public.member_loading_caption') }}</span>
                        </div>
                    </template>

                    <template v-if="users?.length > 0">
                        <Column
                            field="name"
                            :header="$t('public.admin')"
                       
                            sortable
                        >
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-surface-950 dark:text-white">{{ data.name }}</span>
                                    <span class="text-surface-500">{{ data.email }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="role"
                            :header="$t('public.role')"
                            class="hidden md:table-cell"
                        >
                            <template #body="{data}">
                                {{ $t(`public.${data.role}`) }}
                            </template>
                        </Column>

                        <Column
                            field="scope_of_permissions"
                            :header="$t('public.scope_of_permissions')"
                            class="hidden md:table-cell"
                        >
                            <template #body="{data}">
                                <Tag
                                    :severity="data.permissions_count === permissionsCount ? 'success' : 'danger'"
                                    :value="data.permissions_count === permissionsCount ? $t('public.full_access') : $t('public.limited')"
                                />
                            </template>
                        </Column>

                        <Column
                            field="mobile"
                            class="table-cell md:hidden"
                        >
                            <template #body="{data}">
                                <div class="flex items-center gap-3 justify-end w-full">
                                    <div class="flex flex-col items-start">
                                        <div class="flex items-center gap-1 justify-end w-full">
                                            <Tag
                                                :severity="data.permissions_count === permissionsCount ? 'success' : 'danger'"
                                                :value="data.permissions_count === permissionsCount ? $t('public.full_access') : $t('public.limited')"
                                            />
                                        </div>

                                        <div class="flex justify-end w-full">
                                            {{ $t(`public.${data.role}`) }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>

                        <Column
                            field="action"
                            style="width: 5%"
                        >
                            <template #body="{data}">
                                <AdminTableAction 
                                    :admin="data"
                                    :permissions="props.permissions"
                                />
                            </template>
                        </Column>
                    </template>
                </DataTable>
            </div>
        </template>
    </Card>
</template>
