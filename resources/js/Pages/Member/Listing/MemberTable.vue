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
import { ref } from 'vue';
import dayjs from 'dayjs';
import MemberTableAction from './MemberTableAction.vue';

const props = defineProps({
  user: {
    type: Array,
    required: true
  },
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
    }
};

const statuses = ref(['unverified', 'verified']);

</script>
<template>
    <div class="card">
        <DataTable
            :value="$props.user" 
            paginator 
            :rows="10" 
            dataKey="id"
            filterDisplay="menu"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
            :rowsPerPageOptions="[10, 20, 50, 100]"
            :globalFilterFields="['name', 'email']"
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

            <template #empty> No user found. </template>

            <template #loading> Loading user data. Please wait. </template>

            <Column
                field="created_at"
                header="JOINED"
                style="min-width: 9rem"
                sortable
            >
                <template #body="{ data }">
                    {{ dayjs(data.created_at).format('YYYY-MM-DD') }}
                </template>
            </Column>
            
            <Column
                field="name"
                header="NAME" 
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
                header="EMAIL" 
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
                header="REFERER"
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
                header="RANK"
                style="min-width: 10rem"
                sortable
            >
                
            </Column>

            <Column
                field="country.name"
                header="COUNTRY"
                style="min-width: 12rem"
                sortable
            >
                <template #body="{data}">
                    <div class="flex flex-col items-start">
                        <div class="flex items-center gap-1">
                            <span>{{ data.country.emoji }}</span>
                            <span>{{ data.country.name }}</span>
                        </div>
                    </div>
                </template>

                <template #filter="{ filterModel }">
                    <InputText v-model="filterModel.value" type="text" placeholder="Search by country" />
                </template>
            </Column>

            <Column
                field="role"
                header="ROLE"
                style="min-width: 10rem"
                sortable
            >
                <template #body="{ data }">
                    {{ data.role }}
                </template>
            </Column>

            <Column
                field="kyc_status"
                header="STATUS"
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
                   <MemberTableAction :member="data" />
                </template>
            </Column>
        </DataTable>
    </div>
</template>