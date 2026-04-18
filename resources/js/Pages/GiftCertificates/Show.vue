<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    certificate: Object,
});

const categoryLabel = computed(() => ({
    horeca: 'HoReCa',
    retail: 'Розничная торговля',
    services: 'Сфера услуг',
}[props.certificate.category] || props.certificate.category));

const statusLabel = computed(() => ({
    draft: 'Черновик',
    active: 'Активен',
    redeemed: 'Использован',
    expired: 'Просрочен',
    cancelled: 'Отменён',
}[props.certificate.status] || props.certificate.status));

/** QR для гашения: кодируем уникальный код сертификата (сканер в ПО точки). */
const qrImageUrl = computed(() => {
    const data = encodeURIComponent(props.certificate.code);
    return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${data}`;
});
</script>

<template>
    <Head :title="`Сертификат ${certificate.code}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ certificate.title || 'Сертификат' }} · {{ certificate.code }}
                </h2>
                <div class="flex gap-3">
                    <Link
                        :href="route('certificates.edit', certificate.id)"
                        class="text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        Редактировать
                    </Link>
                    <Link
                        :href="route('certificates.index')"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        Назад к каталогу
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            Основная информация
                        </h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="shrink-0 text-gray-500">
                                    Код
                                </dt>
                                <dd class="text-right font-mono text-gray-900">
                                    {{ certificate.code }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Категория
                                </dt>
                                <dd class="text-gray-900">
                                    {{ categoryLabel }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Номинал
                                </dt>
                                <dd class="text-gray-900">
                                    {{ certificate.amount }} {{ certificate.currency }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Баланс
                                </dt>
                                <dd class="text-gray-900">
                                    {{ certificate.balance }} {{ certificate.currency }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Статус
                                </dt>
                                <dd class="text-gray-900">
                                    {{ statusLabel }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">
                                    Действует до
                                </dt>
                                <dd class="text-gray-900">
                                    {{ certificate.expires_at ?? '—' }}
                                </dd>
                            </div>
                            <div
                                v-if="certificate.store"
                                class="border-t border-gray-100 pt-3"
                            >
                                <dt class="mb-1 text-gray-500">
                                    Точка гашения
                                </dt>
                                <dd class="text-gray-900">
                                    <div class="font-medium">
                                        {{ certificate.store.name }}
                                    </div>
                                    <div class="text-gray-600">
                                        {{ certificate.store.address }}
                                    </div>
                                    <div
                                        v-if="certificate.store.phone"
                                        class="text-gray-500"
                                    >
                                        {{ certificate.store.phone }}
                                    </div>
                                </dd>
                            </div>
                            <div
                                v-if="certificate.terms_of_use"
                                class="border-t border-gray-100 pt-3"
                            >
                                <dt class="mb-1 text-gray-500">
                                    Условия использования
                                </dt>
                                <dd class="whitespace-pre-wrap text-gray-900">
                                    {{ certificate.terms_of_use }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            QR для гашения
                        </h3>
                        <p class="mb-4 text-xs text-gray-500">
                            Продавец сканирует код в интерфейсе GiftHub; в QR закодирован номер сертификата.
                        </p>
                        <div class="flex justify-center rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <img
                                :src="qrImageUrl"
                                alt="QR-код сертификата"
                                class="h-48 w-48 object-contain"
                                loading="lazy"
                            >
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">
                        Получатель
                    </h3>
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-gray-500">
                                Имя
                            </dt>
                            <dd class="text-gray-900">
                                {{ certificate.recipient_name || '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">
                                Email
                            </dt>
                            <dd class="text-gray-900">
                                {{ certificate.recipient_email || '—' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500">
                                Заметки
                            </dt>
                            <dd class="whitespace-pre-wrap text-gray-900">
                                {{ certificate.notes || '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
