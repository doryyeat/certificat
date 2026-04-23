<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
    certificate: Object,
    amount: Number,
    recipient_email: String,
    recipient_name: String,
    message: String,
});

const form = useForm({
    card_number: '',
    expiry_date: '',
    cvv: '',
    card_holder: '',
});

const loading = ref(false);
const errorMessage = ref('');

const submitPayment = () => {
    loading.value = true;

    // Используйте правильное имя маршрута с префиксом client.
    form.post(route('client.payment.process', { order: props.order.id }), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('client.payment.success'));
        },
        onError: (errors) => {
            errorMessage.value = errors.message || 'Ошибка при оплате';
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 py-12 px-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Оплата заказа</h2>
                    <p class="text-gray-600 mt-2">Заказ №{{ order?.number }}</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Сумма к оплате:</span>
                        <span class="text-2xl font-bold text-purple-600">{{ amount }} BYN</span>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        <p>Сертификат будет отправлен на: {{ recipient_email }}</p>
                        <p v-if="recipient_name">Получатель: {{ recipient_name }}</p>
                    </div>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-6">
                    <!-- Поля карты -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Номер карты</label>
                        <input type="text" v-model="form.card_number" placeholder="0000 0000 0000 0000"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-purple-500 focus:border-purple-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Срок действия</label>
                            <input type="text" v-model="form.expiry_date" placeholder="ММ/ГГ"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CVV/CVC</label>
                            <input type="text" v-model="form.cvv" placeholder="000"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-purple-500 focus:border-purple-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Имя держателя карты</label>
                        <input type="text" v-model="form.card_holder" placeholder="IVAN IVANOV"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-purple-500 focus:border-purple-500" />
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ errorMessage }}
                    </div>

                    <button type="submit" :disabled="loading"
                            class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 disabled:opacity-50">
                        {{ loading ? 'Обработка...' : `Оплатить ${amount} BYN` }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
