<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(
    search,
    () => {
        router.get(
            route('business.customers.index'),
            { search: search.value },
            { preserveState: true, replace: true },
        );
    },
);
</script>

<template>
    <Head title="Клиенты" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Клиенты
                </h2>
                <Link
                    :href="route('business.customers.create')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Новый клиент
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 rounded-lg bg-white p-4 shadow">
                    <label class="block text-sm font-medium text-gray-700">
                        Поиск
                    </label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Имя, email или телефон"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                    Имя
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                    Контакты
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-for="customer in customers.data"
                                :key="customer.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 sm:px-6">
                                    {{ customer.name }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 sm:px-6">
                                    <div v-if="customer.email">
                                        {{ customer.email }}
                                    </div>
                                    <div v-if="customer.phone" class="text-xs text-gray-400">
                                        {{ customer.phone }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm sm:px-6">
                                    <Link
                                        :href="route('business.customers.edit', customer.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Открыть
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!customers.data.length">
                                <td
                                    colspan="3"
                                    class="px-4 py-6 text-center text-sm text-gray-500 sm:px-6"
                                >
                                    Клиенты пока не добавлены.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

