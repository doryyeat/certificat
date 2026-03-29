<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    certificate: Object,
});

const form = useForm({
    amount: props.certificate.amount,
    balance: props.certificate.balance,
    currency: props.certificate.currency,
    expires_at: props.certificate.expires_at
        ? props.certificate.expires_at.substring(0, 10)
        : '',
    recipient_name: props.certificate.recipient_name,
    recipient_email: props.certificate.recipient_email,
    status: props.certificate.status,
    notes: props.certificate.notes,
});

const redeemForm = useForm({
    amount: '',
    description: '',
});

const submit = () => {
    form.put(route('business.certificates.update', props.certificate.id));
};

const redeem = () => {
    redeemForm.post(route('business.certificates.redeem', props.certificate.id), {
        preserveScroll: true,
        onSuccess: () => {
            redeemForm.reset('amount', 'description');
        },
    });
};
</script>

<template>
    <Head :title="`Сертификат ${certificate.code}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Сертификат {{ certificate.code }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Создан {{ new Date(certificate.created_at).toLocaleString() }}
                    </p>
                </div>
                <Link
                    :href="route('certificates.index')"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    Назад к списку
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                Основная информация
                            </h3>

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
                                            for="balance"
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Баланс
                                        </label>
                                        <input
                                            id="balance"
                                            v-model="form.balance"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        />
                                        <div
                                            v-if="form.errors.balance"
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{ form.errors.balance }}
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
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
                                    <div>
                                        <label
                                            for="expires_at"
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Дата окончания
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
                                            for="status"
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Статус
                                        </label>
                                        <select
                                            id="status"
                                            v-model="form.status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="draft">Черновик</option>
                                            <option value="active">Активен</option>
                                            <option value="redeemed">Погашен</option>
                                            <option value="expired">Истёк</option>
                                            <option value="cancelled">Отменён</option>
                                        </select>
                                        <div
                                            v-if="form.errors.status"
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{ form.errors.status }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="notes"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Заметки
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
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        :disabled="form.processing"
                                    >
                                        Сохранить
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                Погашение
                            </h3>
                            <p class="mb-2 text-sm text-gray-500">
                                Текущий баланс:
                                <span class="font-semibold text-gray-900">
                                    {{ certificate.balance }} {{ certificate.currency }}
                                </span>
                            </p>

                            <form @submit.prevent="redeem" class="space-y-4">
                                <div>
                                    <label
                                        for="redeem_amount"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Сумма погашения
                                    </label>
                                    <input
                                        id="redeem_amount"
                                        v-model="redeemForm.amount"
                                        type="number"
                                        min="0"
                                        :max="certificate.balance"
                                        step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    <div
                                        v-if="redeemForm.errors.amount"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ redeemForm.errors.amount }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="redeem_description"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Описание операции
                                    </label>
                                    <input
                                        id="redeem_description"
                                        v-model="redeemForm.description"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    <div
                                        v-if="redeemForm.errors.description"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ redeemForm.errors.description }}
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                    :disabled="redeemForm.processing"
                                >
                                    Погасить
                                </button>
                            </form>
                        </div>

                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                История операций
                            </h3>
                            <ul class="space-y-3">
                                <li
                                    v-for="tx in certificate.transactions"
                                    :key="tx.id"
                                    class="flex items-center justify-between rounded-md border border-gray-100 bg-gray-50 px-3 py-2 text-sm"
                                >
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ tx.type === 'issue' ? 'Выпуск' : tx.type === 'redeem' ? 'Погашение' : 'Корректировка' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ tx.description || '—' }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono text-gray-900">
                                            {{ tx.amount }} {{ certificate.currency }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ new Date(tx.created_at).toLocaleString() }}
                                        </div>
                                    </div>
                                </li>
                                <li v-if="!certificate.transactions?.length" class="text-sm text-gray-500">
                                    Операции ещё не проводились.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

