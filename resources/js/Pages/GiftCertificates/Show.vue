<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    certificate: Object,
});
</script>

<template>
    <Head :title="`Сертификат ${certificate.code}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Сертификат {{ certificate.code }}
                </h2>
                <div class="flex gap-3">
                    <Link
                        :href="route('business.certificates.edit', certificate.id)"
                        class="text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        Редактировать
                    </Link>
                    <Link
                        :href="route('business.certificates.index')"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        Назад к списку
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
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Код</dt>
                                <dd class="font-mono text-gray-900">
                                    {{ certificate.code }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Номинал</dt>
                                <dd class="text-gray-900">
                                    {{ certificate.amount }} {{ certificate.currency }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Баланс</dt>
                                <dd class="text-gray-900">
                                    {{ certificate.balance }} {{ certificate.currency }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Статус</dt>
                                <dd class="text-gray-900">
                                    {{ certificate.status }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Действует до</dt>
                                <dd class="text-gray-900">
                                    {{ certificate.expires_at ?? '—' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">
                            Получатель
                        </h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Имя</dt>
                                <dd class="text-gray-900">
                                    {{ certificate.recipient_name || '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Email</dt>
                                <dd class="text-gray-900">
                                    {{ certificate.recipient_email || '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-gray-500">Заметки</dt>
                                <dd class="whitespace-pre-wrap text-gray-900">
                                    {{ certificate.notes || '—' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

