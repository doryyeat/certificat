<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});
</script>

<template>
    <Head :title="`Заказ ${order.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Заказ {{ order.number }}
                </h2>
                <Link
                    :href="route('orders.index')"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    Назад к заказам
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                Информация о заказе
                            </h3>
                            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-gray-500">
                                        Номер
                                    </dt>
                                    <dd class="font-mono text-gray-900">
                                        {{ order.number }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">
                                        Статус
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{ order.status }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">
                                        Клиент
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{ order.customer?.name || '—' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">
                                        Дата создания
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{ new Date(order.created_at).toLocaleString() }}
                                    </dd>
                                </div>
                            </dl>
                            <div v-if="order.notes" class="mt-4 text-sm">
                                <div class="text-gray-500">
                                    Заметки
                                </div>
                                <div class="mt-1 whitespace-pre-wrap text-gray-900">
                                    {{ order.notes }}
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                Позиции
                            </h3>
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 sm:px-6">
                                            Наименование
                                        </th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 sm:px-6">
                                            Цена
                                        </th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-500 sm:px-6">
                                            Кол-во
                                        </th>
                                        <th class="px-4 py-2 text-right font-medium text-gray-500 sm:px-6">
                                            Сумма
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr
                                        v-for="item in order.items"
                                        :key="item.id"
                                    >
                                        <td class="px-4 py-2 text-gray-900 sm:px-6">
                                            {{ item.name }}
                                        </td>
                                        <td class="px-4 py-2 text-gray-900 sm:px-6">
                                            {{ item.price }} BYN
                                        </td>
                                        <td class="px-4 py-2 text-gray-900 sm:px-6">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="px-4 py-2 text-right text-gray-900 sm:px-6">
                                            {{ item.total }} BYN
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                Итоги
                            </h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">
                                        Сумма товаров
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{ order.total_products }} BYN
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">
                                        Скидка по сертификатам
                                    </dt>
                                    <dd class="text-gray-900">
                                        −{{ order.total_discount }} BYN
                                    </dd>
                                </div>
                                <div class="mt-2 flex justify-between border-t border-gray-200 pt-2 text-base font-semibold">
                                    <dt class="text-gray-900">
                                        К оплате
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{ order.total_amount }} BYN
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div
                            v-if="order.gift_certificates?.length"
                            class="rounded-lg bg-white p-6 shadow"
                        >
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                Использованные сертификаты
                            </h3>
                            <ul class="space-y-3 text-sm">
                                <li
                                    v-for="certificate in order.gift_certificates"
                                    :key="certificate.id"
                                    class="flex items-center justify-between rounded-md border border-gray-100 bg-gray-50 px-3 py-2"
                                >
                                    <div>
                                        <div class="font-mono text-gray-900">
                                            {{ certificate.code }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Номинал: {{ certificate.amount }} {{ certificate.currency }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">
                                            Списано:
                                        </div>
                                        <div class="font-medium text-gray-900">
                                            {{ certificate.pivot.amount_applied }} {{ certificate.currency }}
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

