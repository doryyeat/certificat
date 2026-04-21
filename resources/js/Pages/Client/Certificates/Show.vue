<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Layout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    certificate: Object,
});

const form = useForm({
    payment_method: 'card',
    recipient_email: '',
    recipient_name: '',
    message: '',
});

const submit = () => {
    form.post(route('client.certificates.purchase', props.certificate.id));
};
</script>

<template>
    <Head :title="'Сертификат ' + certificate.code" />

    <Layout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Навигация -->
                <div class="mb-6">
                    <Link :href="route('client.certificates.index')" class="text-purple-600 hover:text-purple-700">
                        ← Назад к списку
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Информация о сертификате -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <div class="h-64 bg-gradient-to-r from-purple-600 to-pink-500 relative">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center text-white">
                                        <div class="text-6xl mb-4">🎁</div>
                                        <div class="text-2xl font-bold">{{ certificate.organization.name }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-8">
                                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                                    {{ certificate.template?.name || 'Подарочный сертификат' }}
                                </h1>

                                <div class="grid grid-cols-2 gap-6 mb-8">
                                    <div>
                                        <div class="text-sm text-gray-500 mb-1">Код сертификата</div>
                                        <div class="text-xl font-mono">{{ certificate.code }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500 mb-1">Номинал</div>
                                        <div class="text-3xl font-bold text-purple-600">{{ certificate.amount }} {{ certificate.currency || 'BYN' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500 mb-1">Действует до</div>
                                        <div>{{ new Date(certificate.expires_at).toLocaleDateString() }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500 mb-1">Получатель</div>
                                        <div>{{ certificate.recipient_name || 'Не указан' }}</div>
                                    </div>
                                </div>

                                <div v-if="certificate.notes" class="border-t pt-6">
                                    <h3 class="text-lg font-semibold mb-2">Описание</h3>
                                    <p class="text-gray-600">{{ certificate.notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Форма покупки -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-lg p-6 sticky top-24">
                            <h2 class="text-xl font-bold mb-6">Оформление покупки</h2>

                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Способ оплаты
                                    </label>
                                    <select v-model="form.payment_method"
                                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                        <option value="card">Банковская карта</option>
                                        <option value="apple_pay">Apple Pay</option>
                                        <option value="google_pay">Google Pay</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Email получателя *
                                    </label>
                                    <input v-model="form.recipient_email"
                                           type="email"
                                           required
                                           class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                           placeholder="email@example.com" />
                                    <p class="text-xs text-gray-500 mt-1">На этот email придет PDF с сертификатом</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Имя получателя *
                                    </label>
                                    <input v-model="form.recipient_name"
                                           type="text"
                                           maxlength="100"
                                           required
                                           class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                           placeholder="Имя получателя" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Поздравительное сообщение
                                    </label>
                                    <textarea v-model="form.message"
                                              rows="3"
                                              class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                              placeholder="Напишите пожелания..."></textarea>
                                </div>

                                <div class="border-t pt-4">
                                    <div class="flex justify-between text-lg font-bold mb-4">
                                        <span>Итого:</span>
                                        <span class="text-purple-600">{{ certificate.amount }} {{ certificate.currency || 'BYN' }}</span>
                                    </div>

                                    <button type="submit"
                                            :disabled="form.processing"
                                            class="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white px-6 py-4 rounded-lg hover:from-purple-700 hover:to-pink-600 transition font-medium text-lg disabled:opacity-50">
                                        {{ form.processing ? 'Обработка...' : 'Оплатить' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
