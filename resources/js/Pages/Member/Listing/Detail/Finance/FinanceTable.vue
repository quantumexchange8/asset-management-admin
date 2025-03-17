<script setup>
import EmptyData from '@/Components/EmptyData.vue';
import { generalFormat } from '@/Composables/format';
import Card from 'primevue/card';
import DataView from 'primevue/dataview';
import Skeleton from 'primevue/skeleton';
import { onMounted, ref } from 'vue';

const props = defineProps({
    user: Object,
});

const first = ref(0);
const rows = ref(5);
const currentPage = ref(1);
const totalRecords = ref(0);
const accumulate = ref();
const isLoading = ref(false);
const { formatAmount } = generalFormat();

const getFinanceData = async (page = 1) => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/member/detail/${props.user.id_number}/financeDetail?page=${page}`);
        
        accumulate.value = response.data.accumulate.data; // Extract paginated data
        totalRecords.value = response.data.accumulate.total;
        console.log(response.data.accumulate.data)
    } catch (error) {
        console.error('Error fetching finance detail:', error);
    } finally {
        isLoading.value = false;
    }
};

const onPage = (event) => {
    first.value = event.first;
    currentPage.value = event.page + 1; 
    getFinanceData(currentPage.value);
};

onMounted(() => {
    getFinanceData();
})
</script>

<template>
    <!-- <Card class="w-full">
        <template #content>
            <div class="w-full">
                <div v-if="isLoading" class="flex flex-col p-6">
                    <div v-for="index in rows" :key="index" class="flex flex-col sm:flex-row sm:items-center p-6 gap-4 border-t border-surface-200 dark:border-surface-700">
                        <div class="flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6">
                            <div class="flex flex-row md:flex-col justify-between items-start gap-2">
                                <div>
                                    <span class="font-medium text-surface-500 dark:text-surface-400 text-sm">
                                        <Skeleton width="5rem" height="2rem"></Skeleton>
                                    </span>
                                    <div class="text-lg font-medium mt-2">
                                        <Skeleton width="5rem" height="2rem"></Skeleton>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col md:items-end gap-8">
                                <span class="text-xl font-semibold">
                                    <Skeleton width="5rem" height="2rem"></Skeleton>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="totalRecords === 0" class="flex flex-col p-6">
                    <EmptyData 
                        :title="$t('public.no_record')"
                    />
                </div>

                <DataView 
                    v-else
                    :value="accumulate"
                    lazy
                    paginator 
                    :rows="rows" 
                    :first="first"
                    :totalRecords="totalRecords"
                    dataKey="id"
                    @page="onPage"
                >
                    <template #list="slotProps">
                        <div class="flex flex-col">
                            <div v-for="(item, index) in slotProps.items" :key="index">
                                <div class="flex flex-col sm:flex-row sm:items-center p-6 gap-4"
                                    :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }">
                                    <div class="flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6">
                                        <div class="flex flex-row md:flex-col justify-between items-start gap-2">
                                            <div>
                                                <span class="font-medium text-surface-500 dark:text-surface-400 text-sm">
                                                    {{ $t(`public.${item.purpose}`) }}
                                                </span>
                                                <div class="text-lg font-medium mt-2">{{ $t(`public.${item.transaction_type}`) }}</div>
                                            </div>
                                            <div class="" style="border-radius: 30px">
                                                <div class="flex items-center gap-2 justify-center">
                                                    <span class="font-medium text-sm">{{ item.created_at }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col md:items-end gap-8">
                                            <span class="text-xl font-semibold">${{ formatAmount(item.amount, 4) ?? 0 }}</span>
                                            <span class="text-xl font-semibold">{{ item.transaction_number}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </DataView>
            </div>
        </template>
    </Card> -->
</template>
