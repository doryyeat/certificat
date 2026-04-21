<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  plan: String,
  maxColors: Number,
  backgroundAllowed: Boolean,
  branding: Object,
})

const form = useForm({
  colors: props.branding?.colors ?? [],
  logo: null,
  background: null,
  remove_logo: false,
  remove_background: false,
})

function addColor() {
  if ((form.colors?.length ?? 0) >= props.maxColors) return
  form.colors.push('#4F46E5')
}

function submit() {
  form.post(route('business.branding.update'), {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Брендирование" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Брендирование</h2>
          <p class="text-sm text-gray-500 mt-1">
            Внешний вид PDF‑сертификата зависит от тарифа (Free/Start/Pro).
          </p>
        </div>
        <a
          class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50"
          :href="route('business.branding.preview')"
          target="_blank"
          rel="noreferrer"
        >
          Предпросмотр PDF
        </a>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
          Тариф: <strong class="uppercase">{{ plan }}</strong>.
          <span v-if="plan === 'free'">В Free применяется только серая схема (настройки сохраняются, но не применяются).</span>
          <span v-else-if="plan === 'start'">Доступны логотип и 1 цвет.</span>
          <span v-else>Доступны логотип, до 3 цветов и фон.</span>
        </div>

        <form class="rounded-lg bg-white p-6 shadow space-y-6" @submit.prevent="submit">
          <div>
            <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-gray-700">Логотип (PNG/JPG, до 5 МБ)</label>
              <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" v-model="form.remove_logo" />
                Удалить
              </label>
            </div>
            <div class="mt-2 flex items-center gap-4">
              <div v-if="branding?.logo_url" class="rounded border p-2 bg-white">
                <img :src="branding.logo_url" class="h-10" alt="logo" />
              </div>
              <input type="file" accept="image/png,image/jpeg" @change="(e) => (form.logo = e.target.files?.[0] ?? null)" />
            </div>
            <div v-if="form.errors.logo" class="text-sm text-red-600 mt-1">{{ form.errors.logo }}</div>
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-gray-700">Цвета (hex)</label>
              <button
                type="button"
                class="text-sm text-indigo-600 hover:text-indigo-800 disabled:opacity-50"
                :disabled="maxColors === 0 || (form.colors?.length ?? 0) >= maxColors"
                @click="addColor"
              >
                Добавить цвет
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">Доступно цветов: {{ maxColors }}</p>

            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div v-for="(c, idx) in form.colors" :key="idx" class="rounded border p-3">
                <div class="text-xs text-gray-500 mb-2">Цвет {{ idx + 1 }}</div>
                <input v-model="form.colors[idx]" class="w-full rounded border-gray-300" placeholder="#4F46E5" />
                <div class="mt-2 h-8 rounded" :style="{ background: form.colors[idx] }" />
              </div>
              <div v-if="maxColors === 0" class="text-sm text-gray-500">
                На Free цветовая схема фиксированная.
              </div>
            </div>
            <div v-if="form.errors.colors" class="text-sm text-red-600 mt-1">{{ form.errors.colors }}</div>
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label class="block text-sm font-medium text-gray-700">Фоновое изображение (только Pro, PNG/JPG, до 10 МБ)</label>
              <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" v-model="form.remove_background" />
                Удалить
              </label>
            </div>
            <div class="mt-2 flex items-center gap-4">
              <div v-if="branding?.background_url" class="rounded border p-2 bg-white">
                <img :src="branding.background_url" class="h-10" alt="background" />
              </div>
              <input type="file" accept="image/png,image/jpeg" :disabled="!backgroundAllowed" @change="(e) => (form.background = e.target.files?.[0] ?? null)" />
            </div>
            <div v-if="form.errors.background" class="text-sm text-red-600 mt-1">{{ form.errors.background }}</div>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800 disabled:opacity-50"
              :disabled="form.processing"
            >
              Сохранить
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

