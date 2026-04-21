<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    templates: Array,
    stores: Array,
    requiresStore: Boolean,
});

const form = useForm({
    template_id: '',
    title: '',
    amount: '',
    currency: 'BYN',
    validity_days: 365,
    category: 'horeca',
    terms_of_use: '',
    store_id: props.stores?.[0]?.id ?? '',
    notes: '',
});

const submit = () => {
    form.post(route('certificates.store'));
};
</script>

<template>
    <Head title="Новый сертификат" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">
                    Новый сертификат (GiftHub)
                </h2>
                <Link
                    :href="route('certificates.index')"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    Назад к каталогу
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div
                    v-if="requiresStore"
                    class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
                >
                    Сначала добавьте точку продаж во вкладке «Магазины» — без неё выпуск сертификата недоступен (ТЗ).
                    <Link
                        :href="route('stores.create')"
                        class="ml-1 font-medium text-amber-950 underline"
                    >
                        Добавить точку
                    </Link>
                </div>

                <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                    <form
                        class="space-y-6"
                        @submit.prevent="submit"
                    >
                        <div v-if="templates?.length">
                            <label class="block text-sm font-medium text-gray-700">Шаблон (необязательно)</label>
                            <select
                                v-model="form.template_id"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                            >
                                <option value="">
                                    Без шаблона
                                </option>
                                <option
                                    v-for="t in templates"
                                    :key="t.id"
                                    :value="t.id"
                                >
                                    {{ t.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Название для каталога</label>
                            <input
                                v-model="form.title"
                                type="text"
                                maxlength="200"
                                placeholder="Подарочный сертификат"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                            />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Номинал (макс. 1000 BYN)</label>
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    min="0.01"
                                    max="1000"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                                    required
                                >
                                <p
                                    v-if="form.errors.amount"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.amount }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Валюта</label>
                                <input
                                    v-model="form.currency"
                                    type="text"
                                    maxlength="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm uppercase"
                                    required
                                >
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Срок действия, дней (1–1095)</label>
                                <input
                                    v-model="form.validity_days"
                                    type="number"
                                    min="1"
                                    max="1095"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                                    required
                                >
                                <p
                                    v-if="form.errors.validity_days"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.validity_days }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Категория</label>
                                <select
                                    v-model="form.category"
                                    class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                                    required
                                >
                                    <option value="horeca">
                                        HoReCa
                                    </option>
                                    <option value="retail">
                                        Розничная торговля
                                    </option>
                                    <option value="services">
                                        Сфера услуг
                                    </option>
                                    <option value="sport">
                                        Активный отдых и спорт
                                    </option>
                                    <option value="entertainment">
                                        Впечатления и развлечения
                                    </option>
                                    <option value="education">
                                        Обучение и дети
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Магазин (точка гашения)</label>
                            <select
                                v-model="form.store_id"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                                required
                                :disabled="requiresStore"
                            >
                                <option
                                    v-for="s in stores"
                                    :key="s.id"
                                    :value="s.id"
                                >
                                    {{ s.name }} — {{ s.address }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.store_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.store_id }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Условия использования (до 1000 симв.)</label>
                            <textarea
                                v-model="form.terms_of_use"
                                rows="3"
                                maxlength="1000"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Логотип на сертификате — для платных тарифов (загрузка файла можно добавить в хранилище S3/локально).
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Внутренние заметки</label>
                            <textarea
                                v-model="form.notes"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm"
                            />
                        </div>

                        <p class="text-xs text-gray-500">
                            После сохранения система сгенерирует 16-значный код (формат XXXX-XXXX-XXXX-XXXX) и данные для QR (гашение в точке).
                        </p>

                        <div class="flex justify-end gap-3">
                            <Link
                                :href="route('certificates.index')"
                                class="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Отмена
                            </Link>
                            <button
                                type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                :disabled="form.processing || requiresStore"
                            >
                                Создать
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
