<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/ClientLayout.vue';

defineProps({
    orders: Array,
});
</script>

<template>
    <Head title="Мои сертификаты" />

    <Layout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Мои сертификаты</h1>
                    <p class="mt-2 text-gray-600">Все купленные вами сертификаты</p>
                </div>

                <div v-if="orders.length === 0" class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <div class="text-6xl mb-4">🎁</div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">У вас пока нет сертификатов</h2>
                    <p class="text-gray-600 mb-8">Перейдите в каталог и выберите подарок для близких</p>
                    <Link :href="route('client.certificates.index')"
                          class="inline-block bg-gradient-to-r from-purple-600 to-pink-500 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-pink-600 transition font-medium">
                        Перейти в каталог
                    </Link>
                </div>

                <div v-else class="space-y-6">
                    <div v-for="order in orders" :key="order.id" class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="border-b bg-gray-50 px-6 py-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm text-gray-600">Заказ #{{ order.number }}</span>
                                    <span class="ml-4 text-sm text-gray-500">{{ new Date(order.created_at).toLocaleDateString() }}</span>
                                </div>
                                <div class="text-lg font-bold text-purple-600">{{ order.total_amount }} ₽</div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="certificate in order.gift_certificates" :key="certificate.id"
                                     class="border rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="font-semibold">{{ certificate.code }}</h3>
                                            <p class="text-sm text-gray-500">{{ certificate.organization?.name }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full"
                                              :class="{
                                                  'bg-green-100 text-green-800': certificate.status === 'active',
                                                  'bg-gray-100 text-gray-800': certificate.status === 'redeemed',
                                                  'bg-red-100 text-red-800': certificate.status === 'expired',
                                              }">
                                            {{ certificate.status === 'active' ? 'Активен' :
                                            certificate.status === 'redeemed' ? 'Использован' : 'Истек' }}
                                        </span>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Номинал:</span>
                                            <span class="font-medium">{{ certificate.amount }} ₽</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Остаток:</span>
                                            <span class="font-medium">{{ certificate.balance }} ₽</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Действует до:</span>
                                            <span class="font-medium">{{ new Date(certificate.expires_at).toLocaleDateString() }}</span>
                                        </div>
                                    </div>

                                    <Link :href="route('client.my-certificates.show', certificate.id)"
                                          class="mt-4 block w-full text-center bg-purple-100 text-purple-700 px-3 py-2 rounded hover:bg-purple-200 transition text-sm font-medium">
                                        Подробнее
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
