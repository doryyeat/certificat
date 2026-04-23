<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    certificate: Object,
    order: Object,
});

const getStatusColor = (status) => {
    switch(status) {
        case 'active': return 'bg-green-100 text-green-800';
        case 'redeemed': return 'bg-gray-100 text-gray-800';
        case 'expired': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusText = (status) => {
    switch(status) {
        case 'active': return 'Активен';
        case 'redeemed': return 'Использован';
        case 'expired': return 'Истек';
        default: return status;
    }
};
</script>

<template>
    <Head :title="'Сертификат ' + certificate.code" />

    <Layout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Навигация -->
                <div class="mb-6">
                    <Link :href="route('client.my-certificates')" class="text-purple-600 hover:text-purple-700">
                        ← Назад к моим сертификатам
                    </Link>
                </div>

                <!-- Сертификат -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Шапка -->
                    <div class="bg-gradient-to-r from-purple-600 to-pink-500 px-8 py-6 text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-2xl font-bold mb-2">Подарочный сертификат</h1>
                                <p class="opacity-90">{{ certificate.organization?.name }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-4xl font-bold">{{ certificate.amount }} BYN</div>
                                <div class="text-sm opacity-75">номинал</div>
                            </div>
                        </div>
                    </div>

                    <!-- Основная информация -->
                    <div class="p-8">
                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div>
                                <div class="text-sm text-gray-500 mb-1">Код сертификата</div>
                                <div class="text-2xl font-mono font-bold">{{ certificate.code }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 mb-1">Статус</div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium" :class="getStatusColor(certificate.status)">
                                    {{ getStatusText(certificate.status) }}
                                </span>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 mb-1">Действует до</div>
                                <div class="font-semibold">{{ new Date(certificate.expires_at).toLocaleDateString() }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 mb-1">Текущий баланс</div>
                                <div class="text-2xl font-bold text-purple-600">{{ certificate.balance }} BYN</div>
                            </div>
                        </div>

                        <!-- Информация о получателе -->
                        <div class="border-t pt-6 mb-6">
                            <h3 class="text-lg font-semibold mb-4">Информация о получателе</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Имя</div>
                                    <div class="font-medium">{{ certificate.recipient_name || 'Не указано' }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Email</div>
                                    <div class="font-medium">{{ certificate.recipient_email || 'Не указан' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- История транзакций -->
                        <div v-if="certificate.transactions?.length" class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-4">История операций</h3>
                            <div class="space-y-3">
                                <div v-for="transaction in certificate.transactions" :key="transaction.id"
                                     class="flex justify-between items-center py-2 border-b last:border-0">
                                    <div>
                                        <div class="font-medium">
                                            {{ transaction.type === 'issue' ? 'Выпуск' :
                                            transaction.type === 'redeem' ? 'Гашение' : 'Корректировка' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ new Date(transaction.created_at).toLocaleString() }}</div>
                                    </div>
                                    <div class="font-semibold" :class="transaction.type === 'redeem' ? 'text-red-600' : 'text-green-600'">
                                        {{ transaction.type === 'redeem' ? '-' : '+' }}{{ transaction.amount }} BYN
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Информация о заказе -->
                        <div class="border-t pt-6 mt-6">
                            <h3 class="text-lg font-semibold mb-4">Информация о заказе</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Номер заказа</div>
                                    <div class="font-medium">{{ order.number }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Дата покупки</div>
                                    <div class="font-medium">{{ new Date(order.created_at).toLocaleDateString() }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- QR код (заглушка) -->
                        <div class="border-t pt-6 mt-6 text-center">
                            <div class="inline-block p-4 bg-gray-100 rounded-lg">
                                <div class="w-32 h-32 bg-white mx-auto mb-2 flex items-center justify-center">
                                    <span class="text-4xl">📱</span>
                                </div>
                                <p class="text-sm text-gray-600">Покажите этот QR код при оплате</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
