<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    user: Object,
    business: Object,
});

// Безопасное создание формы с проверкой на null
const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    business_name: props.business?.name || '',
    inn: props.business?.inn || '',
    phone: props.business?.phone || '',
    address: props.business?.address || '',
    description: props.business?.description || '',
    website: props.business?.website || '',
});

const submit = () => {
    form.put(route('business.profile.update'), {
        onError: (errors) => {
            console.log('Ошибки валидации:', errors);
        },
        onSuccess: () => {
            form.reset();
        },
    });
};

// Валидация ИНН (только цифры, 10 или 12 символов)
const validateInn = (value) => {
    const cleaned = value.replace(/\D/g, '');
    if (cleaned.length === 10 || cleaned.length === 12) {
        return cleaned;
    }
    return value;
};
</script>

<template>
    <Head title="Редактирование профиля" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Заголовок -->
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-pink-500">
                        <h2 class="text-xl font-semibold text-white">Редактирование профиля</h2>
                        <p class="text-sm text-white/80 mt-1">Заполните информацию о вашей компании</p>
                    </div>

                    <!-- Форма -->
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Информация о владельце -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Информация о владельце
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Имя *</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                        placeholder="Иван Иванов"
                                    />
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Электронная почта *</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                        placeholder="example@mail.com"
                                    />
                                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Информация о компании -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Информация о компании
                            </h3>
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Название компании *</label>
                                        <input
                                            v-model="form.business_name"
                                            type="text"
                                            required
                                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                            placeholder="ООО Пример"
                                        />
                                        <div v-if="form.errors.business_name" class="mt-1 text-sm text-red-600">{{ form.errors.business_name }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">ИНН *</label>
                                        <input
                                            v-model="form.inn"
                                            type="text"
                                            required
                                            maxlength="12"
                                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                            placeholder="1234567890"
                                            @input="form.inn = validateInn($event.target.value)"
                                        />
                                        <div v-if="form.errors.inn" class="mt-1 text-sm text-red-600">{{ form.errors.inn }}</div>
                                        <p class="text-xs text-gray-500 mt-1">10 или 12 цифр</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                                        <input
                                            v-model="form.phone"
                                            type="text"
                                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                            placeholder="+7 (999) 123-45-67"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Веб-сайт</label>
                                        <input
                                            v-model="form.website"
                                            type="url"
                                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                            placeholder="https://example.com"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Адрес</label>
                                    <input
                                        v-model="form.address"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                        placeholder="г. Москва, ул. Примерная, д. 1"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Описание компании</label>
                                    <textarea
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                        placeholder="Расскажите о вашей компании..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Информация о тарифе (только для чтения) -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-600">Текущий тариф:</span>
                                </div>
                                <span class="font-medium text-purple-600">
                                    {{ props.business?.subscription === 'free' ? 'Бесплатный' :
                                    props.business?.subscription === 'start' ? 'Старт' :
                                        props.business?.subscription === 'pro' ? 'Про' : 'Бесплатный' }}
                                </span>
                            </div>
                        </div>

                        <!-- Кнопки -->
                        <div class="border-t pt-6 flex justify-end space-x-4">
                            <Link :href="route('business.profile.show')"
                                  class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Отмена
                            </Link>
                            <button type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg hover:from-purple-700 hover:to-pink-600 transition disabled:opacity-50 flex items-center">
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Сохранение...' : 'Сохранить изменения' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
