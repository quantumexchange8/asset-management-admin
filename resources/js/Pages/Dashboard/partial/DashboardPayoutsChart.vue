<script setup>
import { onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';
import ProgressSpinner from 'primevue/progressspinner';
import { useDark } from "@vueuse/core";
import { IconAlertSmall } from '@tabler/icons-vue';

const props = defineProps({
    selectedYear: Number,
});

const isLoading = ref(false);
const year = ref(props.selectedYear);
const isDarkMode = useDark();

const chartData = ref({
    labels: [],
    datasets: [],
});

let chartInstance = null;

const fetchData = async () => {
    try {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = document.getElementById('dashboardPayoutsChart');

        isLoading.value = true;

        const response = await axios.get('/dashboard/get_payouts', { params: { year: year.value } })

        const { labels, datasets } = response.data.chartData;

        chartData.value.labels = labels;
        chartData.value.datasets = datasets;

        isLoading.value = false

        chartInstance = new Chart(ctx, {
            type: 'bar',
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
                                const roundedValue = parseFloat(value.toFixed(2));
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
                                const formattedValue = formatNumber(roundedValue);
                                return roundedValue === 0 ? '0' : formattedValue;
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
                            color: isDarkMode.value ? "#71717a" : "a1a1aa",
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
        const ctx = document.getElementById('dashboardPayoutsChart');

        isLoading.value = false
        console.error('Error fetching chart data:', error);
    }
}

onMounted(async () => {
    await fetchData();

    watch(
        [() => props.selectedYear, isDarkMode], // Array of expressions to watch
        ([newYear, newTheme]) => {
            // This callback will be called when selectedMonth or selectedYear changes.
            year.value = newYear;
            fetchData();
        }
    );
})
</script>

<template>
    <div class="relative w-full h-[350px] flex items-center justify-center">
        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
            <ProgressSpinner />
        </div>
        <canvas id="dashboardPayoutsChart" height="350" v-show="!isLoading"></canvas>
    </div>
</template>