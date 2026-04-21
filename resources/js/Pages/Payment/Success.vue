<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="mb-6">
                            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>

                        <h1 class="text-2xl font-bold text-gray-900 mb-4">Оплата прошла успешно!</h1>
                        <p class="text-gray-600 mb-6">
                            <span v-if="mail_sent === true">Подарочный сертификат отправлен на email получателя.</span>
                            <span v-else-if="mail_transport === 'log' || mail_transport === 'array'">
                                Отправка email в этом окружении отключена (MAIL_MAILER={{ mail_transport }}). Скачайте PDF ниже.
                            </span>
                            <span v-else-if="mail_sent === false">
                                Не удалось отправить письмо. Скачайте PDF ниже и проверьте настройки почты.
                            </span>
                            <span v-else>Покупка завершена.</span>
                        </p>

                        <div v-if="order" class="bg-gray-50 p-4 rounded-lg mb-6">
                            <p class="text-sm text-gray-600">Заказ №{{ order.number }}</p>
                            <p class="text-lg font-bold text-purple-600">{{ order.total_amount }} BYN</p>
                        </div>

                        <div v-if="certificates?.length" class="bg-white border rounded-lg p-4 mb-6 text-left">
                            <div class="text-sm font-semibold text-gray-900 mb-3">PDF‑файлы сертификатов</div>
                            <div class="space-y-2">
                                <a
                                    v-for="c in certificates"
                                    :key="c.id"
                                    class="inline-flex items-center justify-between w-full px-3 py-2 rounded border hover:bg-gray-50"
                                    :href="route('client.my-certificates.pdf', c.id)"
                                >
                                    <span class="font-mono text-sm">{{ c.code }}</span>
                                    <span class="text-sm text-purple-700">Скачать PDF</span>
                                </a>
                            </div>
                        </div>

                        <div class="space-x-4">
                            <Link :href="route('client.my-certificates')" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                                Мои сертификаты
                            </Link>
                            <Link :href="route('client.certificates.index')" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                Продолжить покупки
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    certificates: Array,
    order: Object,
    mail_sent: [Boolean, null],
    mail_transport: [String, null],
});
</script>
