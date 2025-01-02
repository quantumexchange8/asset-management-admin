<script setup>
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import { IconSearch } from '@tabler/icons-vue';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { onMounted, ref } from 'vue';
import dayjs from 'dayjs';
import MemberTableAction from './MemberTableAction.vue';
import ProgressSpinner from 'primevue/progressspinner';
import Empty from "@/Components/Empty.vue";

const props = defineProps({
    memberCounts: Number
})

const isLoading = ref(false);

//fetch user
const users = ref([]);
const fetchUsers = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/member/get_member_data');
        users.value = response.data.user;
    } catch (error) {
        console.error('Error fetching users:', error);
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    fetchUsers();
});

//catch the emitted new event(refreshTable) from membertableaction.vue
const handleRefreshTable = () => {
    fetchUsers();  // Refresh the data when the event is caught
};

//filteration
const filters = ref();
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        email: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'upline.name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'country.name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        kyc_status: { operator: FilterOperator.OR, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    };
};

initFilters();

const clearFilter = () => {
    initFilters();
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

const statuses = ref(['unverified', 'verified', 'pending']);
</script>

<template>
    <div class="card">
        <DataTable
            :value="users" 
            paginator 
            :rows="10" 
            dataKey="id"
            filterDisplay="menu"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
            :rowsPerPageOptions="[10, 20, 50, 100]"
            :globalFilterFields="['name', 'email', 'username']"
            tableStyle="md:min-width: 50rem"
            v-model:filters="filters"
            removableSort
            ref="dt"
            :loading="isLoading"
        >
            <template #header>
                <div class="flex justify-between items-center">
                        <Button type="button" label="Clear" outlined @click="clearFilter()" />
                        <div class="flex items-center space-x-4">
                            <!-- Search bar -->
                            <IconField>
                                <InputIcon>
                                    <IconSearch size="16" stroke-width="1.5"/>
                                </InputIcon>
                                <InputText v-model="filters['global'].value" placeholder="Keyword Search" />
                            </IconField>
                        </div>
                    </div>
            </template>

            <template #empty>
                <Empty
                    :title="'Member Listing'"
                    :message="'No member found'"
                />
            </template>

            <template #loading>
                <div class="flex flex-col gap-2 items-center justify-center">
                    <ProgressSpinner
                        strokeWidth="4"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Loading user data. Please wait. </span>
                </div>
            </template>

            <template v-if="users?.length > 0">
                <Column
                    field="created_at"
                    header="joined"
                    style="min-width: 9rem"
                    sortable
                >
                    <template #body="{ data }">
                        {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                    </template>
                </Column>
                
                <Column
                    field="name"
                    header="name" 
                    style="min-width: 12rem"
                    sortable
                    frozen
                >
                    <template #body="{ data }">
                        {{ data.name }}
                    </template>

                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                    </template>
                </Column>

                <Column
                    field="email"
                    header="email" 
                    style="min-width: 12rem"
                    sortable
                >
                    <template #body="{ data }">
                        {{ data.email }}
                    </template>

                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" placeholder="Search by email" />
                    </template>
                </Column>

                <Column
                    field="upline.name"
                    header="referer"
                    style="min-width: 12rem"
                    sortable
                >
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

                <template #filter="{ filterModel }">
                    <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                </template>
                
                </Column>

                <Column
                    field="rank.rank_name"
                    header="rank"
                    style="min-width: 10rem"
                    sortable
                >
                    <template #body="{ data }">
                        {{ data.rank.rank_name }}
                    </template>
                </Column>

                <Column
                    field="role"
                    header="role"
                    style="min-width: 10rem"
                    sortable
                >
                    <template #body="{ data }">
                        {{ data.role }}
                    </template>
                </Column>

                <Column
                    field="country.name"
                    header="country"
                    style="min-width: 12rem"
                    sortable
                >
                    <template #body="{data}">
                        <span>{{ data.country.name }}</span>             
                    </template>

                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" placeholder="Search by country" />
                    </template>
                </Column>

                <Column
                    field="kyc_status"
                    header="status"
                    sortable
                >
                    <template #body="{ data }">
                        <Tag :value="data.kyc_status" :severity="getSeverity(data.kyc_status)" />
                    </template>

                    <template #filter="{ filterModel }">
                        <Select v-model="filterModel.value" :options="statuses" placeholder="Select One" showClear>
                            <template #option="slotProps">
                                <Tag :value="slotProps.option" :severity="getSeverity(slotProps.option)" />
                            </template>
                        </Select>
                    </template>
                </Column>

                <Column
                    field="action"
                    frozen
                    alignFrozen="right"
                    header=""
                    style="width: 5%"
                    class="hidden md:table-cell"
                >
                    <template #body="{data}">
                    <MemberTableAction :member="data" @refreshTable="handleRefreshTable"/>
                    </template>
                </Column>
            </template>
        </DataTable>
    </div>
</template>