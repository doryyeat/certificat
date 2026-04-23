<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const status = ref(props.filters.status || '');
const search = ref(props.filters.search || '');

watch(
    [status, search],
    () => {
        router.get(
            route('orders.index'),
            { status: status.value, search: search.value },
            { preserveState: true, replace: true },
        );
    },
    { deep: true },
);
</script>

<template>
    <Head title="Заказы" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Заказы
                </h2>
                <Link
                    :href="route('orders.create')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Новый заказ
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 rounded-lg bg-white p-4 shadow sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Поиск
                        </label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Номер заказа или клиент"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Статус
                        </label>
                        <select
                            v-model="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">Все</option>
                            <option value="draft">Черновик</option>
                            <option value="pending">Ожидает оплаты</option>
                            <option value="paid">Оплачен</option>
                            <option value="cancelled">Отменён</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                    Номер
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                    Клиент
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                    Сумма
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                    Статус
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                    Дата
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="order in orders.data"
                                :key="order.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-mono text-indigo-700 sm:px-6">
                                    <Link
                                        :href="route('orders.show', order.id)"
                                        class="hover:underline"
                                    >
                                        {{ order.number }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 sm:px-6">
                                    {{ order.customer?.name || '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 sm:px-6">
                                    {{ order.total_amount }} BYN
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm sm:px-6">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="{
                                            'bg-gray-100 text-gray-700': order.status === 'draft',
                                            'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                            'bg-green-100 text-green-800': order.status === 'paid',
                                            'bg-red-100 text-red-800': order.status === 'cancelled',
                                        }"
                                    >
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500 sm:px-6">
                                    {{ new Date(order.created_at).toLocaleString() }}
                                </td>
                            </tr>
                            <tr v-if="!orders.data.length">
                                <td
                                    colspan="5"
                                    class="px-4 py-6 text-center text-sm text-gray-500 sm:px-6"
                                >
                                    Заказы пока не созданы.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

