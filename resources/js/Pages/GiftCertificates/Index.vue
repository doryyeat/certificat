<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    certificates: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch(
    [search, status],
    () => {
        router.get(
            route('business.certificates.index'),
            {
                search: search.value,
                status: status.value,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    },
    { deep: true },
);

const statusLabel = computed(() => ({
    draft: 'Черновик',
    active: 'Активен',
    redeemed: 'Погашен',
    expired: 'Истёк',
    cancelled: 'Отменён',
}));

const statusColor = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'redeemed':
            return 'bg-blue-100 text-blue-800';
        case 'expired':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Подарочные сертификаты" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Подарочные сертификаты
                </h2>
                <Link
                    :href="route('business.certificates.create')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Новый сертификат
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 grid gap-4 rounded-lg bg-white p-4 shadow sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Поиск
                        </label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Код, получатель, email"
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
                            <option value="active">Активен</option>
                            <option value="draft">Черновик</option>
                            <option value="redeemed">Погашен</option>
                            <option value="expired">Истёк</option>
                            <option value="cancelled">Отменён</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6"
                                >
                                    Код
                                </th>
                                <th
                                    scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6"
                                >
                                    Получатель
                                </th>
                                <th
                                    scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6"
                                >
                                    Сумма / Баланс
                                </th>
                                <th
                                    scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6"
                                >
                                    Статус
                                </th>
                                <th
                                    scope="col"
                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6"
                                >
                                    Действует до
                                </th>
                                <th
                                    scope="col"
                                    class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6"
                                >
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="certificate in certificates.data"
                                :key="certificate.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-mono text-gray-900 sm:px-6">
                                    {{ certificate.code }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 sm:px-6">
                                    <div class="font-medium text-gray-900">
                                        {{ certificate.recipient_name || '—' }}
                                    </div>
                                    <div
                                        v-if="certificate.recipient_email"
                                        class="text-xs text-gray-400"
                                    >
                                        {{ certificate.recipient_email }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 sm:px-6">
                                    <div>
                                        {{ certificate.amount }} {{ certificate.currency }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Баланс: {{ certificate.balance }} {{ certificate.currency }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm sm:px-6">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="statusColor(certificate.status)"
                                    >
                                        {{ statusLabel[certificate.status] || certificate.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 sm:px-6">
                                    {{ certificate.expires_at ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm sm:px-6">
                                    <Link
                                        :href="route('business.certificates.edit', certificate.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Открыть
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!certificates.data.length">
                                <td
                                    colspan="6"
                                    class="px-4 py-6 text-center text-sm text-gray-500 sm:px-6"
                                >
                                    Сертификаты пока не созданы.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div
                        v-if="certificates.links && certificates.links.length > 3"
                        class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div
                                v-for="link in certificates.links"
                                :key="link.label"
                            >
                                <button
                                    v-if="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'relative inline-flex items-center border px-3 py-1 text-xs font-medium focus:z-10 focus:outline-none',
                                        link.active
                                            ? 'z-10 border-indigo-500 bg-indigo-50 text-indigo-600'
                                            : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50',
                                    ]"
                                    @click.prevent="router.visit(link.url)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

