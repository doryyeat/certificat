<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  certificate: Object,
})

const redeemForm = useForm({
  amount: '',
})

const balance = computed(() => Number(props.certificate.balance || 0))

function redeemAll() {
  redeemForm.amount = balance.value.toFixed(2)
  submit()
}

function submit() {
  redeemForm.post(route('manager.redeem.submit', props.certificate.id), {
    preserveScroll: true,
    onSuccess: () => redeemForm.reset('amount'),
  })
}
</script>

<template>
  <Head :title="`Сертификат ${certificate.code}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Сертификат {{ certificate.code }}</h2>
          <p class="text-sm text-gray-500 mt-1">
            {{ certificate.organization?.name }} • {{ certificate.store?.address || certificate.store?.name || '—' }}
          </p>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-lg bg-white p-6 shadow">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <div class="text-xs text-gray-500">Статус</div>
              <div class="font-semibold">{{ certificate.status }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Действителен до</div>
              <div class="font-semibold">{{ certificate.expires_at ? new Date(certificate.expires_at).toLocaleDateString() : '—' }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Номинал</div>
              <div class="font-semibold">{{ certificate.amount }} {{ certificate.currency }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Остаток</div>
              <div class="font-semibold">{{ certificate.balance }} {{ certificate.currency }}</div>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Списание</h3>
          <form class="space-y-3" @submit.prevent="submit">
            <div>
              <label class="block text-sm font-medium text-gray-700">Сумма</label>
              <input v-model="redeemForm.amount" type="number" min="0.01" :max="balance" step="0.01" class="mt-1 w-full rounded-lg border-gray-300" required />
              <div v-if="redeemForm.errors.amount" class="text-sm text-red-600 mt-1">{{ redeemForm.errors.amount }}</div>
            </div>
            <button class="w-full px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" :disabled="redeemForm.processing">
              Списать
            </button>
            <button type="button" class="w-full px-4 py-2 rounded-lg border hover:bg-gray-50" @click="redeemAll">
              Списать всё ({{ certificate.balance }})
            </button>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

