<script setup>
import { IconCoin } from "@tabler/icons-vue";
import { generalFormat } from "@/Composables/format.js";
import Card from "primevue/card";
import SelectButton from "primevue/selectbutton";
import DatePicker from "primevue/datepicker";
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import { computed, ref, watch } from "vue";
import DashboardOverviewChart from "./partial/DashboardOverviewChart.vue";

const { formatAmount } = generalFormat();

const date = ref(new Date());

const daysValue = ref('7');
const daysOptions = ref(["7", "14", String(getDaysInMonth(date.value))]); // Ensure all options are strings
const selectedDays = computed(() => Number(daysValue.value));

const totalDeposit = ref(null);
const totalMonthDeposit = ref(null);
const totalWithdrawal = ref(null);
const totalMonthWithdrawal = ref(null);

const handleOverview = (data) => {
    totalDeposit.value = data.totalDeposit;
    totalMonthDeposit.value = data.totalMonthDeposit;
    totalWithdrawal.value = data.totalWithdrawal;
    totalMonthWithdrawal.value = data.totalMonthWithdrawal;
}

// Computed properties to extract the selected month and year
const selectedMonth = computed(() => {
    return date.value ? date.value.getMonth() + 1 : null;
});

const selectedYear = computed(() => {
    return date.value ? date.value.getFullYear() : "";
});

// Function to get the number of days in a selected month
function getDaysInMonth(selectedDate) {
    if (!selectedDate) return "30"; // Return as a string

    const year = selectedDate.getFullYear();
    const month = selectedDate.getMonth() + 1;

    return String(new Date(year, month, 0).getDate()); // Convert number to string
}

// Watch for changes in the DatePicker and update the third option dynamically
watch(date, (newDate) => {
    if (newDate) {
        daysOptions.value[2] = String(getDaysInMonth(new Date(newDate))); // Ensure it's a string
    }
});

// Define tab data
const tabs = ref([
    {
        value: "0",
        label: "deposit",
        icon: "green",
        total: totalDeposit,
        totalMonth: totalMonthDeposit,
    },
    {
        value: "1",
        label: "withdrawal",
        icon: "red",
        total: totalWithdrawal,
        totalMonth: totalMonthWithdrawal,
    },
]);
</script>

<template>
    <Card>
        <template #content>
            <div class="flex flex-col gap-y-4">
                <div class="flex flex-col gap-1 items-start self-stretch">
                    <span class="text-lg font-semibold">{{ $t('public.overview') }}</span>
                    <span class="text-sm text-surface-500">{{ $t('public.overview_caption') }}</span>
                </div>

                <Tabs value="0">
                    <TabList>
                        <Tab v-for="tab in tabs" :key="tab.value" :value="tab.value">
                            {{ $t(`public.${tab.label}`) }}
                        </Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel v-for="tab in tabs" :key="tab.value" :value="tab.value">
                            <!-- Flex container for deposits/withdrawals (with icons) -->
                            <div class="flex gap-5 flex-col sm:flex-row items-start">
                                <!-- Total Deposit/Withdrawal -->
                                <div class="flex gap-5 items-center">
                                    <div class="rounded-full flex items-center justify-center w-10 h-10 bg-primary-300">
                                        <IconCoin size="25" stroke-width="1.25" class="text-primary-700" />
                                    </div>

                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-400 dark:text-gray-500">
                                            {{ $t(`public.total_${tab.label}`) }}
                                        </div>
                                        <div class="text-lg text-gray-900 dark:text-white">
                                            <span v-if="tab.total !== null">
                                                $ {{ formatAmount(tab.total ? tab.total : 0) }}
                                            </span>
                                            <span v-else>
                                                Loading...
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Deposit/Withdrawal (Month/Year) -->
                                <div class="flex gap-5 items-center">
                                    <div :class="`rounded-full flex items-center justify-center w-10 h-10 bg-${tab.icon}-300`">
                                        <IconCoin size="25" stroke-width="1.25" :class="`text-${tab.icon}-600`" />
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="text-sm text-gray-400 dark:text-gray-500">
                                            {{ $t(`public.total_${tab.label}`) }} ({{ $t('public.month') }}: {{ selectedMonth }}, {{ $t('public.year') }}: {{ selectedYear }})
                                        </div>
                                        <div class="text-lg text-gray-900 dark:text-white">
                                            <span v-if="tab.totalMonth !== null">
                                                $ {{ formatAmount(tab.totalMonth ? tab.totalMonth : 0) }}
                                            </span>
                                            <span v-else>
                                                Loading...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabPanel>
                    </TabPanels>
                </Tabs>

                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <!-- Days Select -->
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600 dark:text-gray-300 mb-1">{{ $t('public.days') }}</label>
                        <SelectButton v-model="daysValue" :options="daysOptions" />
                    </div>

                    <!-- Date Picker -->
                    <div class="flex flex-col">
                        <label class="text-sm text-gray-600 dark:text-gray-300 mb-1">{{ $t('public.date') }}</label>
                        <DatePicker v-model="date" view="month" dateFormat="mm/yy" />
                    </div>
                </div>

                <DashboardOverviewChart 
                    :selectedMonth="selectedMonth"
                    :selectedYear="selectedYear"
                    :selectedDays="selectedDays"
                    @updateTotal="handleOverview"
                />
            </div>
        </template>
    </Card>
</template>