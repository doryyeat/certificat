<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'

const form = useForm({
  code: '',
})

function go() {
  const code = String(form.code || '').trim().toUpperCase()
  if (!code) return
  router.visit(route('manager.redeem.show', code))
}

onMounted(() => {
  const codeFromQuery = new URLSearchParams(window.location.search).get('code')
  if (codeFromQuery) {
    form.code = codeFromQuery
    go()
  }
})
</script>

<template>
  <Head title="Гашение (менеджер)" />

  <AuthenticatedLayout>
    <template #header>
      <div>
        <h2 class="text-xl font-semibold text-gray-800">Гашение сертификата</h2>
        <p class="text-sm text-gray-500 mt-1">Отсканируйте QR или введите код вручную.</p>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Код сертификата</label>
            <input v-model="form.code" class="mt-1 w-full rounded-lg border-gray-300 font-mono" placeholder="AB3F-9K2L-MN7P-QW4R" @keyup.enter="go" />
          </div>
          <button class="w-full px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700" @click="go">
            Открыть
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

