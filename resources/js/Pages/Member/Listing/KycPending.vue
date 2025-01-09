<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import Tag from 'primevue/tag';
import { IconSearch } from '@tabler/icons-vue';
import { IconCheck } from '@tabler/icons-vue';
import { IconX } from '@tabler/icons-vue';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { onMounted, ref } from 'vue';
import dayjs from 'dayjs';
import { useToast } from 'primevue/usetoast';
import ProgressSpinner from 'primevue/progressspinner';
import Empty from "@/Components/Empty.vue";

const isLoading = ref(false);

//fetch user
const users = ref([]);
const fetchUsers = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/member/get_pending_kyc_data');
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

//filteration
const filters = ref();
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        email: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'upline.name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'country.name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    };
};

initFilters();

const clearFilter = () => {
    initFilters();
};

//status severity
const getSeverity = (status) => {
    switch (status) {
        case 'pending':
            return 'info';
    }
};

const toast = useToast();

const submitApprove = async (userId) => {
    try{
        await axios.put(`/member/${userId}/kycApprove`);
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Status approved successfully!',
            life: 3000,
        });
        await fetchUsers(); 
    } catch(error){
        console.error(error);
    }
}

const submitReject = async (userId) => {
    try{
        await axios.put(`/member/${userId}/kycReject`)
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Status rejected successfully!',
            life: 3000,
        });
        await fetchUsers(); 
    } catch(error){
        console.error(error);
    }
}
</script>

<template>
    <AuthenticatedLayout :title="'Pending Kyc'">
        <div class="space-y-4"> <!-- Adds vertical gap between child elements -->
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
                    :globalFilterFields="['name', 'email']"
                    v-model:filters="filters"
                    removableSort
                    ref="dt"
                    tableStyle="md:min-width: 50rem"
                   :loading="isLoading"
                >
                    <template #header>
                        <div class="flex flex-wrap justify-between items-center">
                            <div class="flex items-center space-x-4 w-full md:w-auto">
                                <Button type="button" label="Clear" outlined @click="clearFilter()" />
                            
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
                            :message="'No pending member found'"
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
                            header="referrer"
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
                            style="min-width: 8rem"
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
                            style="min-width: 8rem"
                        >
                            <template #body="{ data }">
                                <Tag :value="data.kyc_status" :severity="getSeverity(data.kyc_status)" />
                            </template>
                        </Column>

                        <Column
                            field="action"
                            header="action"
                        >
                            <template #body="{data}">
                                <div class="flex items-center self-stretch gap-x-2">
                                    <Button
                                        size="sm"
                                        type="button"
                                        class="bg-transparent border-none p-0 m-0 outline-none focus:outline-none active:outline-none hover:bg-transparent"
                                        @click="submitApprove(data.id)"
                                    >
                                        <IconCheck :size="20" stroke-width="1.5" color="green"/>
                                    </Button>
                                
                                    <Button
                                        class="bg-transparent border-none p-0 m-0 outline-none focus:outline-none active:outline-none hover:bg-transparent"
                                        size="sm"
                                        type="button"
                                        @click="submitReject(data.id)"
                                    >
                                        <IconX :size="20" stroke-width="1.5" color="red"/>
                                    </Button>
                                </div>
                            </template>
                        </Column>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>