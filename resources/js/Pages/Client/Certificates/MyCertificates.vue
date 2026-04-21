<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/ClientLayout.vue';

defineProps({
    certificates: Array,  // Изменено с orders на certificates
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

                <div v-if="certificates.length === 0" class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <div class="text-6xl mb-4">🎁</div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">У вас пока нет сертификатов</h2>
                    <p class="text-gray-600 mb-8">Перейдите в каталог и выберите подарок для близких</p>
                    <Link :href="route('client.certificates.index')"
                          class="inline-block bg-gradient-to-r from-purple-600 to-pink-500 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-pink-600 transition font-medium">
                        Перейти в каталог
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="certificate in certificates" :key="certificate.id"
                         class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-semibold text-lg">{{ certificate.title }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ certificate.code }}</p>
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
                                    <span class="font-medium">{{ certificate.amount }} BYN</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Остаток:</span>
                                    <span class="font-medium text-purple-600">{{ certificate.balance }} BYN</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Действует до:</span>
                                    <span class="font-medium">{{ new Date(certificate.expires_at).toLocaleDateString() }}</span>
                                </div>
                                <div v-if="certificate.store" class="flex justify-between">
                                    <span class="text-gray-600">Магазин:</span>
                                    <span class="font-medium">{{ certificate.store.name }}</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">
                                        Куплен: {{ new Date(certificate.created_at).toLocaleDateString() }}
                                    </span>
                                    <Link :href="route('client.my-certificates.show', certificate.id)"
                                          class="text-purple-600 hover:text-purple-800 font-medium">
                                        Подробнее →
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
