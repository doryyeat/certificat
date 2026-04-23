<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'
import PlatformAdminLayout from "@/Layouts/PlatformAdminLayout.vue";

const props = defineProps({
    stats: Object,
    charts: Object,
    topOrganizations: Array,
    recentActivities: Array,
})

const period = ref('month')
const loading = ref(false)
const chartData = ref(props.charts)
const topOrgs = ref(props.topOrganizations)

// Рефы для графиков
const salesChartRef = ref(null)
const registrationsChartRef = ref(null)
let salesChart = null
let registrationsChart = null

// Загрузка данных через AJAX
async function loadData() {
    loading.value = true
    try {
        const response = await axios.get(route('admin.analytics.data'), {
            params: { period: period.value }
        })
        chartData.value = response.data.charts
        topOrgs.value = response.data.topOrganizations
        setTimeout(() => {
            initCharts()
        }, 100)
    } catch (error) {
        console.error('Failed to load data:', error)
    } finally {
        loading.value = false
    }
}

// Инициализация графиков
function initCharts() {
    // График продаж
    if (salesChartRef.value) {
        if (salesChart) salesChart.destroy()

        salesChart = new Chart(salesChartRef.value, {
            type: 'line',
            data: {
                labels: chartData.value.sales.labels,
                datasets: [
                    {
                        label: 'Количество продаж',
                        data: chartData.value.sales.counts,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Выручка (BYN)',
                        data: chartData.value.sales.revenues,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${ctx.raw.toFixed(2)}`
                        } },
                },
                scales: {
                    y: { title: { display: true, text: 'Количество продаж' }, beginAtZero: true, ticks: { stepSize: 1, precision: 0 } },
                    y1: { position: 'right', title: { display: true, text: 'Выручка (BYN)' }, beginAtZero: true, grid: { drawOnChartArea: false } },
                },
            },
        })
    }

    // График регистраций
    if (registrationsChartRef.value) {
        if (registrationsChart) registrationsChart.destroy()

        registrationsChart = new Chart(registrationsChartRef.value, {
            type: 'bar',
            data: {
                labels: chartData.value.registrations.labels,
                datasets: [{
                    label: 'Новые организации',
                    data: chartData.value.registrations.counts,
                    backgroundColor: '#8B5CF6',
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
            },
        })
    }
}

watch(period, () => loadData())

onMounted(() => {
    setTimeout(() => initCharts(), 100)
})

// Вычисляемые свойства для метрик
const planColors = {
    pro: 'bg-green-100 text-green-800',
    start: 'bg-amber-100 text-amber-800',
    free: 'bg-gray-100 text-gray-800',
}

function getPlanColorClass(plan) {
    return planColors[plan] || 'bg-blue-100 text-blue-800'
}
</script>

<template>
    <Head title="Админ панель" />

    <PlatformAdminLayout>
        <template #title>
            Обзорная панель
        </template>

        <div class="space-y-6">
            <!-- Период -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-white">Аналитика</h1>
                <select v-model="period" class="rounded-lg border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white">
                    <option value="week">Последние 7 дней</option>
                    <option value="month">Последние 30 дней</option>
                    <option value="year">Последний год</option>
                </select>
            </div>

            <!-- Карточки метрик -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-slate-800 p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Организации</p>
                            <p class="text-2xl font-bold text-white">{{ stats.organizations.total }}</p>
                        </div>
                        <div class="rounded-full bg-blue-500/20 p-3">
                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between text-xs">
                        <span class="text-green-400">Активных: {{ stats.organizations.active_subscriptions }}</span>
                        <span class="text-red-400">Просрочено: {{ stats.organizations.expired_subscriptions }}</span>
                    </div>
                </div>

                <div class="rounded-lg bg-slate-800 p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Продано сертификатов</p>
                            <p class="text-2xl font-bold text-white">{{ stats.certificates.total_sold }}</p>
                            <p class="text-xs text-green-400">{{ stats.certificates.total_revenue }} BYN</p>
                        </div>
                        <div class="rounded-full bg-green-500/20 p-3">
                            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2 h-1.5 w-full rounded-full bg-slate-700">
                        <div class="h-1.5 rounded-full bg-green-500" :style="{ width: `${stats.certificates.utilization_rate}%` }"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Использовано: {{ stats.certificates.utilization_rate }}%</p>
                </div>

                <div class="rounded-lg bg-slate-800 p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Заказы</p>
                            <p class="text-2xl font-bold text-white">{{ stats.orders.total_count }}</p>
                            <p class="text-xs text-yellow-400">{{ stats.orders.total_revenue }} BYN</p>
                        </div>
                        <div class="rounded-full bg-yellow-500/20 p-3">
                            <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-slate-400">
                        Средний чек: {{ stats.orders.average_order_value }} BYN
                    </div>
                </div>

                <div class="rounded-lg bg-slate-800 p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Комиссия платформы</p>
                            <p class="text-2xl font-bold text-white">{{ stats.orders.total_commission }} BYN</p>
                        </div>
                        <div class="rounded-full bg-purple-500/20 p-3">
                            <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0v12m0-12H8m0 0V6a2 2 0 012-2h4a2 2 0 012 2v2m-2 0v12" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-slate-400">
                        Бизнес-пользователей: {{ stats.users.business_users }}
                    </div>
                </div>
            </div>

            <!-- Графики -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-slate-800 p-6">
                    <h3 class="mb-4 text-lg font-medium text-white">Динамика продаж</h3>
                    <div style="height: 300px;">
                        <canvas ref="salesChartRef"></canvas>
                    </div>
                </div>

                <div class="rounded-lg bg-slate-800 p-6">
                    <h3 class="mb-4 text-lg font-medium text-white">Регистрации организаций</h3>
                    <div style="height: 300px;">
                        <canvas ref="registrationsChartRef"></canvas>
                    </div>
                </div>
            </div>

            <!-- Продажи по тарифам -->
            <div class="rounded-lg bg-slate-800 p-6">
                <h3 class="mb-4 text-lg font-medium text-white">Продажи по тарифам</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div v-for="plan in charts.sales_by_plan" :key="plan.name" class="rounded-lg bg-slate-700 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-300">{{ plan.name === 'pro' ? 'PRO' : plan.name === 'start' ? 'START' : 'FREE' }}</span>
                            <span class="text-xs text-slate-400">{{ plan.count }} продаж</span>
                        </div>
                        <p class="mt-2 text-xl font-bold text-white">{{ plan.revenue }} BYN</p>
                        <div class="mt-2 h-1.5 w-full rounded-full bg-slate-600">
                            <div class="h-1.5 rounded-full" :style="{ width: `${(plan.revenue / charts.sales_by_plan.reduce((sum, p) => sum + p.revenue, 0)) * 100}%`, backgroundColor: plan.color }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Топ организаций -->
            <div class="rounded-lg bg-slate-800 p-6">
                <h3 class="mb-4 text-lg font-medium text-white">Топ организаций по выручке</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-700">
                        <tr class="text-left text-slate-400">
                            <th class="pb-2">Организация</th>
                            <th class="pb-2">Тариф</th>
                            <th class="pb-2 text-right">Продано</th>
                            <th class="pb-2 text-right">Выручка</th>
                            <th class="pb-2 text-right">Использовано</th>
                            <th class="pb-2 text-right">Остаток</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="org in topOrgs" :key="org.id" class="border-b border-slate-700/50">
                            <td class="py-3 font-medium text-white">{{ org.name }}</td>
                            <td class="py-3">
                                    <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', getPlanColorClass(org.plan_name)]">
                                        {{ org.plan_name === 'pro' ? 'PRO' : org.plan_name === 'start' ? 'START' : 'FREE' }}
                                    </span>
                            </td>
                            <td class="py-3 text-right">{{ org.certificates_sold }}</td>
                            <td class="py-3 text-right text-green-400">{{ org.total_revenue }} BYN</td>
                            <td class="py-3 text-right text-blue-400">{{ org.utilization_rate }}%</td>
                            <td class="py-3 text-right text-yellow-400">{{ org.remaining_balance }} BYN</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Последние активности -->
            <div class="rounded-lg bg-slate-800 p-6">
                <h3 class="mb-4 text-lg font-medium text-white">Последние активности</h3>
                <div class="space-y-3">
                    <div v-for="activity in recentActivities" :key="activity.id + activity.type" class="flex items-center gap-3 rounded-lg bg-slate-700/50 p-3">
                        <div class="rounded-full bg-slate-600 p-2">
                            <svg v-if="activity.type === 'organization'" class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <svg v-else-if="activity.type === 'request'" class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <svg v-else class="h-4 w-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-white">{{ activity.message }}</p>
                            <p class="text-xs text-slate-400">{{ new Date(activity.created_at || activity.sold_at).toLocaleDateString() }}</p>
                        </div>
                        <div v-if="activity.amount" class="text-sm font-medium text-green-400">{{ activity.amount }} BYN</div>
                    </div>
                </div>
            </div>
        </div>
    </PlatformAdminLayout>
</template>
