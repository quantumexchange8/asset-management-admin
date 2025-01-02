<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import IconField from 'primevue/iconfield';
import Dialog from 'primevue/dialog';
import FileUpload from 'primevue/fileupload';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import InputNumber from 'primevue/inputnumber';
import DatePicker from 'primevue/datepicker';
import Tag from 'primevue/tag';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { IconTransferIn } from '@tabler/icons-vue';
import { IconDownload } from '@tabler/icons-vue';
import { IconSearch } from '@tabler/icons-vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import dayjs from 'dayjs';
import Empty from "@/Components/Empty.vue";
import ProgressSpinner from 'primevue/progressspinner';

const props = defineProps({
withdrawalHistory: {
    type: Array,
    required: true
  }
});

const visible = ref(false);
const fileupload = ref(null);   

//filteration
const filters = ref();
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        'user.name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        amount: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
        approval_at: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.DATE_IS }] },
    };
};

const clearFilter = () => {
    initFilters();
};

initFilters();

//status severity
const getSeverity = (status) => {
    switch (status) {
        
        case 'success':
            return 'success';
    }
};
</script>

<template>
    <AuthenticatedLayout :title="'Withdrawal History'">
        <div class="card">
            <DataTable 
                :value="props.withdrawalHistory" 
                paginator 
                :rows="10" 
                dataKey="id"
                filterDisplay="menu"
                :globalFilterFields="['user.name', 'transaction_number', 'amount', 'fund_type', 'status', 'approval_at']"
                v-model:filters="filters"
                removableSort
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
                        :title="'Withdrawal History'"
                        :message="'No withdrawal history found'"
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

                <template v-if="props.withdrawalHistory?.length > 0">
                    <Column 
                        field="transaction_number"
                        header="Transaction Number" 
                        style="min-width: 12rem"
                        sortable
                    >
                    
                    </Column>

                    <Column 
                        field="user.name"
                        header="Name" 
                        style="min-width: 12rem"
                        sortable
                    >
                        <template #body="{ data }">
                            {{ data.user.name }}
                        </template>

                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                        </template>
                    </Column>

                    <Column 
                        field="amount"
                        header="Amount" 
                        style="min-width: 12rem"
                        dataType="numeric"
                        sortable
                    >
                        <template #body="{ data }">
                            {{ data.amount }}
                        </template>

                        <template #filter="{ filterModel }">
                            <InputNumber v-model="filterModel.value" mode="currency" currency="USD" locale="en-US" />
                        </template>
                    </Column>

                    <Column 
                        field="fund_type"
                        header="Fund Type" 
                        style="min-width: 12rem"
                        sortable
                    >
                    
                    </Column>

                    <Column 
                        field="status"
                        header="Status" 
                        style="min-width: 12rem"
                        sortable
                    >
                        <template #body="{ data }">
                            <Tag :value="data.status" :severity="getSeverity(data.status)" />
                        </template>
                    </Column>

                    <Column 
                        field="approval_at"
                        header="ApprovalAt" 
                        style="min-width: 12rem"
                        dataType="date"
                    
                        sortable
                    >
                        <template #body="{ data }">
                            {{ dayjs(data.approval_at).format('YYYY-MM-DD') }}
                        </template>
                        <!-- <template #filter="{ filterModel }">
                            <DatePicker v-model="filterModel.value" dateFormat="yy-mm-dd" placeholder="yy-mm-dd" />
                        </template> -->
                    </Column>
                </template>
            </DataTable>
        </div>
    </AuthenticatedLayout>
</template>