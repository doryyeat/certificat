<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    amount: '',
    currency: 'RUB',
    expires_at: '',
    recipient_name: '',
    recipient_email: '',
    notes: '',
});

const submit = () => {
    form.post(route('business.certificates.store'));
};
</script>

<template>
    <Head title="Новый сертификат" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Новый сертификат
                </h2>
                <Link
                    :href="route('business.certificates.index')"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    Назад к списку
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="amount"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Номинал
                                </label>
                                <input
                                    id="amount"
                                    v-model="form.amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div
                                    v-if="form.errors.amount"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.amount }}
                                </div>
                            </div>
                            <div>
                                <label
                                    for="currency"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Валюта
                                </label>
                                <input
                                    id="currency"
                                    v-model="form.currency"
                                    type="text"
                                    maxlength="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 uppercase shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div
                                    v-if="form.errors.currency"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.currency }}
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="recipient_name"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Имя получателя
                                </label>
                                <input
                                    id="recipient_name"
                                    v-model="form.recipient_name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div
                                    v-if="form.errors.recipient_name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.recipient_name }}
                                </div>
                            </div>
                            <div>
                                <label
                                    for="recipient_email"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Email получателя
                                </label>
                                <input
                                    id="recipient_email"
                                    v-model="form.recipient_email"
                                    type="email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div
                                    v-if="form.errors.recipient_email"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.recipient_email }}
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="expires_at"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Дата окончания (необязательно)
                                </label>
                                <input
                                    id="expires_at"
                                    v-model="form.expires_at"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div
                                    v-if="form.errors.expires_at"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.expires_at }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                for="notes"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Заметки (внутренние)
                            </label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                            <div
                                v-if="form.errors.notes"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.notes }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link
                                :href="route('business.certificates.index')"
                                class="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Отмена
                            </Link>
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                :disabled="form.processing"
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

