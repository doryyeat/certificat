<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  stores: { type: Array, default: () => [] },
})

const storageKey = 'pos_redemption_queue_v1'
const isOnline = ref(typeof navigator !== 'undefined' ? navigator.onLine : true)
const syncing = ref(false)

const form = useForm({
  store_id: props.stores?.[0]?.id ?? null,
  code: '',
  amount: '',
})

const queue = ref([])

function loadQueue() {
  try {
    queue.value = JSON.parse(localStorage.getItem(storageKey) || '[]')
  } catch {
    queue.value = []
  }
}

function saveQueue() {
  localStorage.setItem(storageKey, JSON.stringify(queue.value))
}

function enqueueRedemption(payload) {
  queue.value.unshift(payload)
  saveQueue()
}

async function syncQueue() {
  if (!isOnline.value || syncing.value || queue.value.length === 0) return
  syncing.value = true
  try {
    // Отправляем по одному, сохраняя порядок
    for (const item of [...queue.value].reverse()) {
      await new Promise((resolve, reject) => {
        router.post(
          route('business.pos.redeem.submit'),
          item,
          {
            preserveScroll: true,
            onSuccess: () => resolve(),
            onError: () => reject(new Error('sync failed')),
          },
        )
      })
      queue.value = queue.value.filter((q) => q.offline_id !== item.offline_id)
      saveQueue()
    }
  } finally {
    syncing.value = false
  }
}

function submit() {
  const payload = {
    store_id: form.store_id,
    code: String(form.code || '').trim(),
    amount: form.amount,
    qr_data: String(form.code || '').trim(),
  }

  if (!payload.store_id || !payload.code || !payload.amount) return

  if (!isOnline.value) {
    enqueueRedemption({
      ...payload,
      offline_id: `off_${Date.now()}_${Math.random().toString(16).slice(2)}`,
      redeemed_at: new Date().toISOString(),
    })
    form.reset('code', 'amount')
    return
  }

  form.post(route('business.pos.redeem.submit'), {
    preserveScroll: true,
    onSuccess: () => form.reset('code', 'amount'),
  })
}

onMounted(() => {
  loadQueue()
  window.addEventListener('online', () => { isOnline.value = true; syncQueue() })
  window.addEventListener('offline', () => { isOnline.value = false })
  syncQueue()
})

const storeOptions = computed(() =>
  (props.stores || []).map((s) => ({ id: s.id, label: s.address ? `${s.name} — ${s.address}` : s.name })),
)
</script>

<template>
  <div class="max-w-3xl mx-auto p-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold">Гашение сертификата (POS)</h1>
        <p class="text-sm text-gray-500 mt-1">
          Сканируйте QR или введите код вручную. При отсутствии интернета операции сохраняются и синхронизируются позже.
        </p>
      </div>
      <div class="text-right">
        <div class="text-sm">
          Статус: <span :class="isOnline ? 'text-green-600' : 'text-amber-600'">{{ isOnline ? 'онлайн' : 'офлайн' }}</span>
        </div>
        <div class="text-xs text-gray-500">
          В очереди: {{ queue.length }} <span v-if="syncing">(синхронизация...)</span>
        </div>
      </div>
    </div>

    <div class="mt-6 bg-white rounded-xl border p-5">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-3">
          <label class="block text-sm font-medium text-gray-700">Точка продаж</label>
          <select v-model="form.store_id" class="mt-1 w-full rounded-lg border-gray-300">
            <option v-for="o in storeOptions" :key="o.id" :value="o.id">{{ o.label }}</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Код/данные QR</label>
          <input v-model="form.code" class="mt-1 w-full rounded-lg border-gray-300" placeholder="XXXX-XXXX-XXXX-XXXX" />
          <div v-if="form.errors.code" class="text-sm text-red-600 mt-1">{{ form.errors.code }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Сумма списания</label>
          <input v-model="form.amount" class="mt-1 w-full rounded-lg border-gray-300" placeholder="10.00" />
          <div v-if="form.errors.amount" class="text-sm text-red-600 mt-1">{{ form.errors.amount }}</div>
        </div>
      </div>

      <div class="mt-5 flex items-center gap-3">
        <button
          type="button"
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white disabled:opacity-50"
          :disabled="form.processing"
          @click="submit"
        >
          Списать
        </button>
        <button
          type="button"
          class="px-4 py-2 rounded-lg border"
          :disabled="!isOnline || syncing || queue.length === 0"
          @click="syncQueue"
        >
          Синхронизировать очередь
        </button>
      </div>
    </div>
  </div>
</template>

