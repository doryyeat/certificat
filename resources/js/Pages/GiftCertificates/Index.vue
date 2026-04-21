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
const category = ref(props.filters.category || '');
const amountMin = ref(props.filters.amount_min || '');
const amountMax = ref(props.filters.amount_max || '');
const expiresFrom = ref(props.filters.expires_from || '');
const expiresTo = ref(props.filters.expires_to || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortDir = ref(props.filters.sort_dir || 'desc');

watch(
    [search, status, category, amountMin, amountMax, expiresFrom, expiresTo, sortBy, sortDir],
    () => {
        router.get(
            route('certificates.index'),
            {
                search: search.value,
                status: status.value,
                category: category.value,
                amount_min: amountMin.value,
                amount_max: amountMax.value,
                expires_from: expiresFrom.value,
                expires_to: expiresTo.value,
                sort_by: sortBy.value,
                sort_dir: sortDir.value,
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
    redeemed: 'Использован',
    expired: 'Просрочен',
    cancelled: 'Отменён',
}));

const categoryLabel = (cat) => ({
    horeca: 'HoReCa',
    retail: 'Розница',
    services: 'Услуги',
    sport: 'Активный отдых и спорт',
    entertainment: 'Впечатления и развлечения',
    education: 'Обучение и дети',
}[cat] || cat);

const statusColor = (s) => {
    switch (s) {
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
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">
                        Каталог сертификатов
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Карточки, фильтры и поиск по ТЗ GiftHub
                    </p>
                </div>
                <Link
                    :href="route('certificates.create')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                >
                    Добавить
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 grid gap-4 rounded-lg bg-white p-4 shadow sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Поиск
                        </label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Название, условия…"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Статус
                        </label>
                        <select
                            v-model="status"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">
                                Все
                            </option>
                            <option value="active">
                                Активен
                            </option>
                            <option value="draft">
                                Черновик
                            </option>
                            <option value="redeemed">
                                Использован
                            </option>
                            <option value="expired">
                                Просрочен
                            </option>
                            <option value="cancelled">
                                Отменён
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Категория
                        </label>
                        <select
                            v-model="category"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">
                                Все
                            </option>
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Номинал от
                        </label>
                        <input
                            v-model="amountMin"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Номинал до
                        </label>
                        <input
                            v-model="amountMax"
                            type="number"
                            min="0"
                            step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Действует с
                        </label>
                        <input
                            v-model="expiresFrom"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Действует по
                        </label>
                        <input
                            v-model="expiresTo"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Сортировка
                        </label>
                        <select
                            v-model="sortBy"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="created_at">По новизне</option>
                            <option value="amount">По номиналу</option>
                            <option value="balance">По остатку</option>
                            <option value="expires_at">По сроку действия</option>
                            <option value="status">По статусу</option>
                            <option value="title">По названию</option>
                            <option value="code">По коду</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Направление
                        </label>
                        <select
                            v-model="sortDir"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="desc">По убыванию</option>
                            <option value="asc">По возрастанию</option>
                        </select>
                    </div>
                </div>

                <div
                    v-if="certificates.data?.length"
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="certificate in certificates.data"
                        :key="certificate.id"
                        class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md"
                    >
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <h3 class="line-clamp-2 font-semibold text-gray-900">
                                {{ certificate.title || 'Подарочный сертификат' }}
                            </h3>
                            <span
                                class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusColor(certificate.status)"
                            >
                                {{ statusLabel[certificate.status] || certificate.status }}
                            </span>
                        </div>
                        <p class="mb-1 text-2xl font-bold text-indigo-600">
                            {{ certificate.amount }} {{ certificate.currency }}
                        </p>
                        <p class="mb-2 text-xs text-gray-500">
                            Баланс: {{ certificate.balance }} {{ certificate.currency }}
                        </p>
                        <div class="mb-3 flex flex-wrap gap-2 text-xs">
                            <span class="rounded bg-gray-100 px-2 py-0.5 text-gray-700">
                                {{ categoryLabel(certificate.category) }}
                            </span>
                            <span
                                v-if="certificate.validity_days"
                                class="rounded bg-gray-100 px-2 py-0.5 text-gray-700"
                            >
                                {{ certificate.validity_days }} дн.
                            </span>
                        </div>
                        <p
                            v-if="certificate.store"
                            class="mb-4 line-clamp-2 text-xs text-gray-500"
                        >
                            {{ certificate.store.name }} · {{ certificate.store.address }}
                        </p>
                        <p
                            v-else
                            class="mb-4 text-xs text-gray-400"
                        >
                            Точка не указана
                        </p>
                        <p class="mb-4 text-xs text-gray-500">
                            До {{ certificate.expires_at ?? '—' }}
                        </p>
                        <div class="mt-auto flex flex-wrap gap-2 border-t border-gray-100 pt-4">
                            <Link
                                :href="route('certificates.show', certificate.id)"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                            >
                                Подробнее
                            </Link>
                            <Link
                                :href="route('certificates.edit', certificate.id)"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Редактировать
                            </Link>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-lg border border-dashed border-gray-300 bg-white p-12 text-center text-sm text-gray-500"
                >
                    Сертификаты не найдены. Создайте первый или измените фильтры.
                </div>

                <div
                    v-if="certificates.links && certificates.links.length > 3"
                    class="mt-6 flex flex-wrap items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3"
                >
                    <button
                        v-for="link in certificates.links"
                        :key="link.label"
                        v-html="link.label"
                        type="button"
                        :class="[
                            'relative inline-flex items-center border px-3 py-1 text-xs font-medium focus:z-10 focus:outline-none',
                            link.active
                                ? 'z-10 border-indigo-500 bg-indigo-50 text-indigo-600'
                                : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50',
                        ]"
                        :disabled="!link.url"
                        @click.prevent="link.url && router.visit(link.url)"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
