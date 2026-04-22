<script setup>
import { computed, onMounted, ref, watch, shallowRef } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// Регистрируем все компоненты Chart.js
Chart.register(...registerables)

const group = ref('day')
const from = ref('')
const to = ref('')
const loading = ref(false)
const payload = ref(null)
const error = ref('')

// Рефы для графиков
const salesChartRef = ref(null)
const revenueChartRef = ref(null)
let salesChart = null
let revenueChart = null

async function load() {
    loading.value = true
    error.value = ''
    try {
        const { data } = await axios.get(route('business.analytics.data'), {
            params: { group: group.value, from: from.value || undefined, to: to.value || undefined },
        })
        payload.value = data.data
        // После загрузки данных обновляем графики
        setTimeout(() => {
            initCharts()
        }, 100)
    } catch (e) {
        error.value = 'Не удалось загрузить аналитику.'
        console.error(e)
    } finally {
        loading.value = false
    }
}

function initCharts() {
    const salesData = salesSeries.value
    const dates = salesData.map(item => item.bucket)
    const salesCounts = salesData.map(item => item.count)
    const revenueTotals = salesData.map(item => item.total)

    // Уничтожаем старые графики, если они существуют
    if (salesChart) {
        salesChart.destroy()
    }
    if (revenueChart) {
        revenueChart.destroy()
    }

    // График количества продаж
    if (salesChartRef.value) {
        salesChart = new Chart(salesChartRef.value, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Количество продаж',
                        data: salesCounts,
                        borderColor: '#4F46E5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#4F46E5',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Продаж: ${context.parsed.y} шт.`
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Количество продаж',
                        },
                        ticks: {
                            stepSize: 1,
                        },
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Период',
                        },
                    },
                },
            },
        })
    }

    // График выручки
    if (revenueChartRef.value) {
        revenueChart = new Chart(revenueChartRef.value, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Выручка',
                        data: revenueTotals,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: '#10B981',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Выручка: ${context.parsed.y.toFixed(2)} BYN`
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Выручка (BYN)',
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2)
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Период',
                        },
                    },
                },
            },
        })
    }
}

onMounted(() => {
    const now = new Date()
    const prior = new Date()
    prior.setMonth(now.getMonth() - 1)
    to.value = now.toISOString().slice(0, 10)
    from.value = prior.toISOString().slice(0, 10)
    load()
})

watch([group, from, to], () => {
    load()
})

const summary = computed(() => payload.value?.summary)
const salesSeries = computed(() => payload.value?.series?.sales || [])
const redeemSeries = computed(() => payload.value?.series?.redeems || [])
const nominal = computed(() => payload.value?.nominal_popularity || [])

function exportCsv() {
    window.location.href = route('business.analytics.export.csv', { from: from.value, to: to.value })
}

function exportPdf() {
    window.location.href = route('business.analytics.export.pdf', { from: from.value, to: to.value })
}

function exportBuyers() {
    window.location.href = route('business.analytics.buyers.csv', { from: from.value, to: to.value })
}
</script>

<template>
    <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-semibold">Аналитика</h1>
                <p class="text-sm text-gray-500 mt-1">Продажи, выручка, гашения и комиссия по выбранному периоду.</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button class="px-4 py-2 rounded-lg border hover:bg-gray-50 transition" @click="exportCsv">
                     Экспорт CSV
                </button>
                <button class="px-4 py-2 rounded-lg border hover:bg-gray-50 transition" @click="exportPdf">
                     Экспорт PDF
                </button>
                <button class="px-4 py-2 rounded-lg border hover:bg-gray-50 transition" @click="exportBuyers">
                     Покупатели CSV
                </button>
            </div>
        </div>

        <!-- Карточки с метриками -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl border bg-white p-4 shadow-sm">
                <div class="text-xs text-gray-500">Количество продаж</div>
                <div class="text-2xl font-semibold mt-1 text-indigo-600">{{ summary?.sales_count ?? '—' }}</div>
            </div>
            <div class="rounded-xl border bg-white p-4 shadow-sm">
                <div class="text-xs text-gray-500">Выручка</div>
                <div class="text-2xl font-semibold mt-1 text-green-600">
                    {{ summary ? Number(summary.revenue).toFixed(2) : '—' }} BYN
                </div>
            </div>
            <div class="rounded-xl border bg-white p-4 shadow-sm">
                <div class="text-xs text-gray-500">Использованные сертификаты</div>
                <div class="text-2xl font-semibold mt-1 text-orange-600">{{ summary?.used_certificates ?? '—' }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    на сумму: {{ summary ? Number(summary.used_amount).toFixed(2) : '—' }} BYN
                </div>
            </div>
            <div class="rounded-xl border bg-white p-4 shadow-sm">
                <div class="text-xs text-gray-500">Удержанная комиссия</div>
                <div class="text-2xl font-semibold mt-1 text-red-600">
                    {{ summary ? Number(summary.retained_commission).toFixed(2) : '—' }} BYN
                    <span class="text-xs text-gray-500" v-if="summary">({{ summary.fee_percent }}%)</span>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="mt-6 rounded-xl border bg-white p-5 shadow-sm">
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs text-gray-600 font-medium">Группировка</label>
                    <select v-model="group" class="mt-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="day">По дням</option>
                        <option value="week">По неделям</option>
                        <option value="month">По месяцам</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 font-medium">С</label>
                    <input v-model="from" type="date" class="mt-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-xs text-gray-600 font-medium">По</label>
                    <input v-model="to" type="date" class="mt-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div class="flex items-center gap-2">
                    <div v-if="loading" class="text-sm text-gray-500">
                        <svg class="animate-spin h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Загрузка...
                    </div>
                    <div v-if="error" class="text-sm text-red-600">{{ error }}</div>
                </div>
            </div>

            <!-- Графики -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- График продаж -->
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-semibold mb-4"> Динамика продаж</div>
                    <div style="height: 300px;">
                        <canvas ref="salesChartRef"></canvas>
                    </div>
                </div>

                <!-- График выручки -->
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-semibold mb-4"> Динамика выручки</div>
                    <div style="height: 300px;">
                        <canvas ref="revenueChartRef"></canvas>
                    </div>
                </div>
            </div>

            <!-- Таблица данных -->
            <div class="mt-6">
                <div class="text-sm font-semibold mb-3"> Детализация по периодам</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="py-2 px-3 border-b">Период</th>
                            <th class="py-2 px-3 border-b text-right">Продаж</th>
                            <th class="py-2 px-3 border-b text-right">Выручка</th>
                            <th class="py-2 px-3 border-b text-right">Средний чек</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="r in salesSeries" :key="r.bucket" class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">{{ r.bucket }}</td>
                            <td class="py-2 px-3 text-right">{{ r.count }}</td>
                            <td class="py-2 px-3 text-right font-medium text-green-600">
                                {{ Number(r.total).toFixed(2) }} BYN
                            </td>
                            <td class="py-2 px-3 text-right">
                                {{ r.count > 0 ? Number(r.total / r.count).toFixed(2) : '0.00' }} BYN
                            </td>
                        </tr>
                        <tr v-if="salesSeries.length === 0">
                            <td class="py-2 px-3 text-gray-500 text-center" colspan="4">Нет данных за выбранный период</td>
                        </tr>
                        </tbody>
                        <tfoot v-if="salesSeries.length > 0" class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="py-2 px-3">Итого</td>
                            <td class="py-2 px-3 text-right">{{ salesSeries.reduce((sum, r) => sum + r.count, 0) }}</td>
                            <td class="py-2 px-3 text-right text-green-600">
                                {{ salesSeries.reduce((sum, r) => sum + r.total, 0).toFixed(2) }} BYN
                            </td>
                            <td class="py-2 px-3 text-right">
                                {{ (salesSeries.reduce((sum, r) => sum + r.total, 0) / salesSeries.reduce((sum, r) => sum + r.count, 0)).toFixed(2) }} BYN
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Популярность номиналов -->
            <div class="mt-6">
                <div class="text-sm font-semibold mb-3"> Популярность номиналов</div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <div v-for="n in nominal" :key="n.amount" class="rounded-lg border p-3 text-center hover:shadow-md transition">
                        <div class="text-xs text-gray-500">Номинал</div>
                        <div class="text-lg font-bold text-indigo-600">{{ Number(n.amount).toFixed(0) }} BYN</div>
                        <div class="text-xs text-gray-500 mt-1">Продано: {{ n.count }} шт.</div>
                    </div>
                    <div v-if="nominal.length === 0" class="text-sm text-gray-500 col-span-full text-center py-4">
                        Нет данных за выбранный период.
                    </div>
                </div>
            </div>
        </div>
    </div>
        </AuthenticatedLayout>
</template>

<style scoped>
canvas {
    max-width: 100%;
}
</style>
