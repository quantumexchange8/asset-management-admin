<script setup>
import { onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';
import ProgressSpinner from 'primevue/progressspinner';
import {useDark} from "@vueuse/core";

const props = defineProps({
    selectedMonth: Number,
    selectedYear: Number,
    selectedDays: Number,
});

const chartData = ref({
    labels: [],
    datasets: [],
});

const isLoading = ref(false);
const days = ref(props.selectedDays);
const month = ref(props.selectedMonth);
const year = ref(props.selectedYear);
const isDarkMode = useDark();

let chartInstance = null;

const fetchData = async () => {
    try {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = document.getElementById('dashboardOverviewChart');

        isLoading.value = true;

        const response = await axios.get('/dashboard/get_total_deposit_by_days', { params: { days: days.value, month: month.value, year: year.value } });
        const { labels, datasets } = response.data.chartData;

        chartData.value.labels = labels;
        chartData.value.datasets = datasets;

        isLoading.value = false

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: chartData.value,
            options: {
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: {
                        ticks: {
                            callback: function (value) {
                                const ranges = [
                                    { divider: 1e6, suffix: 'M' },
                                    { divider: 1e3, suffix: 'k' }
                                ];
                                function formatNumber(n) {
                                    for (let i = 0; i < ranges.length; i++) {
                                        if (n >= ranges[i].divider) {
                                            return (n / ranges[i].divider).toString() + ranges[i].suffix;
                                        }
                                    }
                                    return n;
                                }
                                return formatNumber(value);
                            },
                            color: isDarkMode.value ? "#71717a" : "#a1a1aa",
                            font: {
                                family: "Inter, sans-serif",
                                size: 14,
                                weight: 400,
                            },
                        },
                        suggestedMin: 1000,
                        grace: "50%",
                        beginAtZero: true,
                        border: { display: false },
                        grid: {
                            drawTicks: false,
                            color: isDarkMode.value ? "#3f3f46" : "#e4e4e7",
                        },
                    },
                    x: {
                        ticks: {
                            color: isDarkMode.value ? "#71717a" : "#a1a1aa",
                            font: {
                                family: "Inter, sans-serif",
                                size: 14,
                                weight: 400,
                            },
                        },
                        grid: {
                            drawTicks: true,
                            color: "transparent",
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                }
            }
        });


    } catch (error) {
        const ctx = document.getElementById('dashboardOverviewChart');

        isLoading.value = false
        console.error('Error fetching chart data:', error);
    }
}

onMounted(async () => {
    await fetchData(); // Fetch data on mount

    watch(
        [() => props.selectedDays, () => props.selectedMonth, () => props.selectedYear, isDarkMode], // Array of expressions to watch
        ([newDay, newMonth, newYear, newTheme]) => {
            // This callback will be called when selectedMonth or selectedYear changes.
            days.value = newDay;
            month.value = newMonth;
            year.value = newYear;
            fetchData();
        }
    );

});
</script>

<template>
    <div class="relative w-full h-[350px] flex items-center justify-center">
        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
            <ProgressSpinner />
        </div>
        <canvas id="dashboardOverviewChart" height="350" v-show="!isLoading"></canvas>
    </div>
</template>
