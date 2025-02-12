<script setup>
import { onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';
import ProgressSpinner from 'primevue/progressspinner';

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
const totalMonthDeposit = ref();
const totalDeposit = ref();
const totalMonthWithdrawal = ref();
const totalWithdrawal = ref();

const emit = defineEmits(['updateTotal'])

let chartInstance = null;

const fetchData = async () => {
    try {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = document.getElementById('TotalDeposit');

        isLoading.value = true;

        const response = await axios.get('/dashboard/get_total_deposit_by_days', { params: { days: days.value, month: month.value, year: year.value } });
        const { labels, datasets } = response.data.chartData;
        totalMonthDeposit.value = response.data.totalMonthDeposit
        totalDeposit.value = response.data.totalDeposit

        totalMonthWithdrawal.value = response.data.totalMonthWithdrawal
        totalWithdrawal.value = response.data.totalWithdrawal

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
                                var ranges = [
                                    { divider: 1e6, suffix: 'M' },
                                    { divider: 1e3, suffix: 'k' }
                                ];
                                function formatNumber(n) {
                                    for (var i = 0; i < ranges.length; i++) {
                                        if (n >= ranges[i].divider) {
                                            return (n / ranges[i].divider).toString() + ranges[i].suffix;
                                        }
                                    }
                                    return n;
                                }
                                return formatNumber(value);
                            },
                            color: '#9DA4AE',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 14,
                                weight: 400,
                            },
                        },
                        grace: '10%',
                        beginAtZero: true,
                        border: {
                            display: false
                        },
                        grid: {
                            drawTicks: false,
                            color: (ctx) => {
                                return '#D0D5DD'
                            }
                        },
                    },
                    x: {
                        ticks: {
                            color: '#9DA4AE',
                            font: {
                                family: 'Inter, sans-serif',
                                size: 14,
                                weight: 400,
                            },
                        },
                        grid: {
                            drawTicks: false,
                            color: (ctx) => {
                                return 'transparent'
                            }
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#9DA4AE'
                        }
                    },
                }
            }
        });


    } catch (error) {
        const ctx = document.getElementById('TotalDeposit');

        isLoading.value = false
        console.error('Error fetching chart data:', error);
    }
}

// Emit the totals whenever they change
watch([totalMonthDeposit, totalDeposit, totalMonthWithdrawal, totalWithdrawal], () => {
    emit('updateTotal', {
        totalMonthDeposit: totalMonthDeposit.value,
        totalDeposit: totalDeposit.value,
        totalMonthWithdrawal: totalMonthWithdrawal.value,
        totalWithdrawal: totalWithdrawal.value,
    });
});

onMounted(async () => {
    await fetchData(); // Fetch data on mount

    // Watch for changes in the date and fetch data when it changes

    watch(
        [() => props.selectedDays, () => props.selectedMonth, () => props.selectedYear], // Array of expressions to watch
        ([newDay, newMonth, newYear]) => {
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
        <canvas id="TotalDeposit" height="350" v-show="!isLoading"></canvas>
    </div>
</template>
