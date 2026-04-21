<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  managers: Array,
})

const form = useForm({
  name: '',
  email: '',
  phone: '',
  generate_password: true,
  password: '',
  send_credentials: true,
})

function submit() {
  form.post(route('business.managers.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset('name', 'email', 'phone', 'password'),
  })
}

function sendCredentials(id) {
  router.post(route('business.managers.send', id), {}, { preserveScroll: true })
}

function removeManager(id) {
  if (!confirm('Удалить менеджера?')) return
  router.delete(route('business.managers.destroy', id), { preserveScroll: true })
}
</script>

<template>
  <Head title="Менеджеры" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Менеджеры</h2>
          <p class="text-sm text-gray-500 mt-1">
            Менеджер может гасить сертификаты по QR/коду.
          </p>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-lg bg-white p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Добавить менеджера</h3>

          <form class="grid grid-cols-1 md:grid-cols-2 gap-4" @submit.prevent="submit">
            <div>
              <label class="block text-sm font-medium text-gray-700">Имя</label>
              <input v-model="form.name" class="mt-1 w-full rounded-lg border-gray-300" maxlength="100" required />
              <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border-gray-300" required />
              <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Телефон (опц.)</label>
              <input v-model="form.phone" class="mt-1 w-full rounded-lg border-gray-300" />
            </div>
            <div class="space-y-2">
              <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" v-model="form.generate_password" />
                Сгенерировать пароль
              </label>
              <div v-if="!form.generate_password">
                <label class="block text-sm font-medium text-gray-700">Пароль</label>
                <input v-model="form.password" type="text" class="mt-1 w-full rounded-lg border-gray-300" minlength="6" />
                <div v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</div>
              </div>
              <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" v-model="form.send_credentials" />
                Отправить данные для входа на почту
              </label>
            </div>

            <div class="md:col-span-2 flex justify-end">
              <button class="px-4 py-2 rounded-lg bg-gray-900 text-white disabled:opacity-50" :disabled="form.processing">
                Добавить
              </button>
            </div>
          </form>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
          <h3 class="text-lg font-semibold mb-4">Список менеджеров</h3>

          <div v-if="!managers?.length" class="text-sm text-gray-500">Пока нет менеджеров.</div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-gray-500">
                  <th class="py-2 pr-4">Имя</th>
                  <th class="py-2 pr-4">Email</th>
                  <th class="py-2 pr-4">Телефон</th>
                  <th class="py-2 pr-4"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in managers" :key="m.id" class="border-t">
                  <td class="py-2 pr-4">{{ m.name }}</td>
                  <td class="py-2 pr-4">{{ m.email }}</td>
                  <td class="py-2 pr-4">{{ m.phone || '—' }}</td>
                  <td class="py-2 pr-4 text-right space-x-2">
                    <button class="px-3 py-1 rounded border hover:bg-gray-50" @click="sendCredentials(m.id)">
                      Отправить доступы
                    </button>
                    <button class="px-3 py-1 rounded border text-red-700 hover:bg-red-50" @click="removeManager(m.id)">
                      Удалить
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

