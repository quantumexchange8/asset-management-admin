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
  depositHistory: {
    type: Array,
    required: true
  }
});

const visible = ref(false);
const fileupload = ref(null);   

const upload = () => {
    if (fileupload.value) {
        const file = fileupload.value.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('file', file);

            // Send the file to the server
            router.post('/transaction/history/import-deposit-history', formData, {
                onSuccess: () => {
                    console.log('File uploaded successfully');
                    visible.value = false;
                },
                onError: (error) => {
                    console.error('Error uploading file:', error);
                },
            });
        }
    }
};

const onFileUpload = (event) => {
    console.log('File uploaded:', event);
};

//filteration
const filters = ref();
const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        'user.name': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        amount: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
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
    <AuthenticatedLayout :title="'Deposit History'">
        <div class="card">
            <DataTable 
                :value="props.depositHistory" 
                paginator 
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50, 100]" 
                dataKey="id"
                filterDisplay="menu"
                :globalFilterFields="['user.name', 'transaction_number', 'amount', 'fund_type', 'status', 'approval_at']"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                v-model:filters="filters"
                removableSort
                tableStyle="md:min-width: 50rem"
            >
                <template #header>
                   <div class="flex flex-wrap justify-between items-center">
                        <!-- Left side (Clear button + Search bar) -->
                        <div class="flex items-center space-x-4 w-full md:w-auto">
                            <!-- Clear Button -->
                            <Button type="button" label="Clear" outlined @click="clearFilter()" />

                            <!-- Search bar -->
                            <IconField>
                                <InputIcon>
                                    <IconSearch size="16" stroke-width="1.5"/>
                                </InputIcon>

                                <InputText v-model="filters['global'].value" placeholder="Keyword Search" />
                            </IconField>
                        </div>

                        <!-- Right side (Export + Import buttons) -->
                        <div class="flex items-center space-x-4 w-full md:w-auto mt-4 md:mt-0">
                            <!-- Export button -->
                            <a :href="route('transaction.history.exportDepositHistory')" class="w-full md:w-auto">
                                <Button class="flex justify-center items-center w-full">
                                    <span class="pr-1">Export</span>
                                    <IconDownload size="16" stroke-width="1.5"/>
                                </Button>
                            </a>

                            <!-- Import button -->
                            <Button class="w-full md:w-auto flex justify-center items-center" @click="visible = true">
                                <span class="pr-1">Import</span>
                                <IconTransferIn size="16" stroke-width="1.5"/>
                            </Button>

                            <!-- Modal -->
                             
                            <Dialog v-model:visible="visible" modal header="Import Deposit History" :style="{ width: '25rem' }">

                                <span class="text-surface-500 dark:text-surface-400 block mb-3 text-sm font-medium">
                                    Excel file only. Supported formats: .xlsx, .xls, .csv
                                </span>

                                <div class="flex items-center gap-4 mb-8">
                                    <!-- File upload aligned to the left with more spacing -->
                                    <FileUpload 
                                        ref="fileupload" 
                                        mode="basic" 
                                        name="file"                                        
                                        accept=".xlsx, .xls, .csv" 
                                        :maxFileSize="25000"         
                                        :customUpload="true" 
                                        @upload="onFileUpload" 
                                        class="w-full" 
                                    />
                                </div>

                                <div class="flex justify-end gap-2">
                                    <Button type="button" label="Cancel" severity="secondary" @click="visible = false" />
                                    <Button label="Upload" @click="upload" />
                                </div>
                            </Dialog>
                        </div>
                    </div>
                </template>

                <template #empty>
                        <Empty
                            :title="'Deposit History'"
                            :message="'No deposit history found'"
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

                <template v-if="props.depositHistory?.length > 0">
                    <Column 
                        field="transaction_number"
                        header="transaction number" 
                        style="min-width: 12rem"
                        sortable
                    >
                    
                    </Column>

                    <Column 
                        field="user.name"
                        header="name" 
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
                        header="amount" 
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
                        header="fund type" 
                        style="min-width: 12rem"
                        sortable
                    >
                    
                    </Column>

                    <Column 
                        field="status"
                        header="status" 
                        style="min-width: 12rem"
                        sortable
                    >
                        <template #body="{ data }">
                            <Tag :value="data.status" :severity="getSeverity(data.status)" />
                        </template>
                    </Column>

                    <Column 
                        field="approval_at"
                        header="approval at" 
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