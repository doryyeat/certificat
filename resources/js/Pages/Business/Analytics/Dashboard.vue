<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'

const group = ref('day') // day|week|month
const from = ref('')
const to = ref('')
const loading = ref(false)
const payload = ref(null)
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await axios.get(route('business.analytics.data'), {
      params: { group: group.value, from: from.value || undefined, to: to.value || undefined },
    })
    payload.value = data.data
  } catch (e) {
    error.value = 'Не удалось загрузить аналитику.'
  } finally {
    loading.value = false
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

watch([group, from, to], () => load())

const summary = computed(() => payload.value?.summary)
const salesSeries = computed(() => payload.value?.series?.sales || [])
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
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold">Аналитика</h1>
        <p class="text-sm text-gray-500 mt-1">Продажи, выручка, гашения и комиссия по выбранному периоду.</p>
      </div>
      <div class="flex gap-2">
        <button class="px-4 py-2 rounded-lg border" @click="exportCsv">Экспорт CSV</button>
        <button class="px-4 py-2 rounded-lg border" @click="exportPdf">Экспорт PDF</button>
        <button class="px-4 py-2 rounded-lg border" @click="exportBuyers">Экспорт покупателей (CSV)</button>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="rounded-xl border bg-white p-4">
        <div class="text-xs text-gray-500">Количество продаж</div>
        <div class="text-2xl font-semibold mt-1">{{ summary?.sales_count ?? '—' }}</div>
      </div>
      <div class="rounded-xl border bg-white p-4">
        <div class="text-xs text-gray-500">Выручка</div>
        <div class="text-2xl font-semibold mt-1">{{ summary ? Number(summary.revenue).toFixed(2) : '—' }}</div>
      </div>
      <div class="rounded-xl border bg-white p-4">
        <div class="text-xs text-gray-500">Использованные сертификаты</div>
        <div class="text-2xl font-semibold mt-1">{{ summary?.used_certificates ?? '—' }}</div>
      </div>
      <div class="rounded-xl border bg-white p-4">
        <div class="text-xs text-gray-500">Удержанная комиссия</div>
        <div class="text-2xl font-semibold mt-1">
          {{ summary ? Number(summary.retained_commission).toFixed(2) : '—' }}
          <span class="text-xs text-gray-500" v-if="summary">({{ summary.fee_percent }}%)</span>
        </div>
      </div>
    </div>

    <div class="mt-6 rounded-xl border bg-white p-5">
      <div class="flex flex-wrap gap-3 items-end">
        <div>
          <label class="block text-xs text-gray-600">Группировка</label>
          <select v-model="group" class="mt-1 rounded-lg border-gray-300">
            <option value="day">День</option>
            <option value="week">Неделя</option>
            <option value="month">Месяц</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-600">С</label>
          <input v-model="from" type="date" class="mt-1 rounded-lg border-gray-300" />
        </div>
        <div>
          <label class="block text-xs text-gray-600">По</label>
          <input v-model="to" type="date" class="mt-1 rounded-lg border-gray-300" />
        </div>
        <div class="text-sm text-gray-500 mb-1" v-if="loading">Загрузка…</div>
        <div class="text-sm text-red-600 mb-1" v-if="error">{{ error }}</div>
      </div>

      <div class="mt-4">
        <div class="text-sm font-semibold">Динамика продаж</div>
        <div class="mt-2 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500">
                <th class="py-2 pr-4">Период</th>
                <th class="py-2 pr-4">Продаж</th>
                <th class="py-2 pr-4">Выручка</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in salesSeries" :key="r.bucket" class="border-t">
                <td class="py-2 pr-4">{{ r.bucket }}</td>
                <td class="py-2 pr-4">{{ r.count }}</td>
                <td class="py-2 pr-4">{{ Number(r.total).toFixed(2) }}</td>
              </tr>
              <tr v-if="salesSeries.length === 0" class="border-t">
                <td class="py-2 text-gray-500" colspan="3">Нет данных за выбранный период.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-6">
        <div class="text-sm font-semibold">Популярность номиналов</div>
        <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-3">
          <div v-for="n in nominal" :key="n.amount" class="rounded-lg border p-3">
            <div class="text-xs text-gray-500">Номинал</div>
            <div class="text-lg font-semibold">{{ Number(n.amount).toFixed(2) }}</div>
            <div class="text-xs text-gray-500 mt-1">Продано: {{ n.count }}</div>
          </div>
          <div v-if="nominal.length === 0" class="text-sm text-gray-500">Нет данных.</div>
        </div>
      </div>
    </div>
  </div>
</template>

