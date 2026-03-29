<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    user: Object,
    business: Object,
});

// Безопасные вычисляемые свойства
const userName = computed(() => props.user?.name || 'Не указано');
const userEmail = computed(() => props.user?.email || 'Не указан');
const businessName = computed(() => props.business?.name || 'Не указано');
const businessInn = computed(() => props.business?.inn || 'Не указан');
const businessPhone = computed(() => props.business?.phone || 'Не указан');
const businessAddress = computed(() => props.business?.address || 'Не указан');
const businessWebsite = computed(() => props.business?.website || 'Не указан');
const businessDescription = computed(() => props.business?.description || 'Описание отсутствует');
const businessSubscription = computed(() => props.business?.subscription || 'free');
const isVerified = computed(() => props.business?.is_verified || false);

// Статистика с безопасными значениями
const certificatesCount = computed(() => props.business?.certificates_count || 0);
const activeCertificatesCount = computed(() => props.business?.active_certificates_count || 0);
const locationsCount = computed(() => props.business?.locations_count || 0);
const certificatesLimit = computed(() => props.business?.subscription_details?.certificates_limit || '∞');

const getSubscriptionBadge = (subscription) => {
    switch(subscription) {
        case 'free': return 'bg-gray-100 text-gray-800';
        case 'start': return 'bg-blue-100 text-blue-800';
        case 'pro': return 'bg-purple-100 text-purple-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getSubscriptionText = (subscription) => {
    switch(subscription) {
        case 'free': return 'Бесплатный';
        case 'start': return 'Старт';
        case 'pro': return 'Про';
        default: return 'Бесплатный';
    }
};

// Инициалы для аватара
const initials = computed(() => {
    if (businessName.value && businessName.value !== 'Не указано') {
        return businessName.value.charAt(0).toUpperCase();
    }
    return userName.value.charAt(0).toUpperCase();
});
</script>

<template>
    <Head title="Профиль компании" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок с уведомлением о необходимости заполнения -->
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Профиль компании</h1>
                        <p class="mt-2 text-gray-600">Управление информацией о вашем бизнесе</p>
                    </div>
                    <Link :href="route('business.profile.edit')"
                          class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-pink-600 transition font-medium">
                        Редактировать профиль
                    </Link>
                </div>

                <!-- Баннер для незаполненного профиля -->
                <div v-if="!props.business?.name || !props.business?.inn"
                     class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Ваш профиль не полностью заполнен. Пожалуйста, добавьте информацию о компании.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Левая колонка - Лого и статус -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <div class="h-32 bg-gradient-to-r from-purple-600 to-pink-500"></div>
                            <div class="px-6 pb-6 text-center -mt-12">
                                <div class="w-24 h-24 mx-auto rounded-full bg-white p-1 shadow-lg">
                                    <div class="w-full h-full rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center text-4xl text-white">
                                        {{ initials }}
                                    </div>
                                </div>
                                <h2 class="text-xl font-bold mt-4">{{ businessName }}</h2>
                                <p class="text-gray-500 text-sm">ИНН: {{ businessInn }}</p>

                                <div class="mt-4 space-y-2">
                                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium"
                                          :class="getSubscriptionBadge(businessSubscription)">
                                        Тариф: {{ getSubscriptionText(businessSubscription) }}
                                    </span>
                                    <span v-if="isVerified"
                                          class="inline-flex ml-2 px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                        ✓ Верифицирован
                                    </span>
                                    <span v-else
                                          class="inline-flex ml-2 px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                        ⏳ На проверке
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Правая колонка - Информация -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Информация о владельце -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Информация о владельце
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Имя</div>
                                    <div class="font-medium">{{ userName }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Email</div>
                                    <div class="font-medium">{{ userEmail }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Информация о компании -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Информация о компании
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm text-gray-500">Название компании</div>
                                    <div class="font-medium">{{ businessName }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">ИНН</div>
                                    <div class="font-medium">{{ businessInn }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Телефон</div>
                                    <div class="font-medium">{{ businessPhone }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Адрес</div>
                                    <div class="font-medium">{{ businessAddress }}</div>
                                </div>
                                <div v-if="businessWebsite && businessWebsite !== 'Не указан'">
                                    <div class="text-sm text-gray-500">Веб-сайт</div>
                                    <a :href="businessWebsite" target="_blank" class="text-purple-600 hover:text-purple-700 font-medium">
                                        {{ businessWebsite }}
                                    </a>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Описание</div>
                                    <div class="mt-1 text-gray-700">{{ businessDescription }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Статистика -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Статистика
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-purple-600">{{ certificatesCount }}</div>
                                    <div class="text-sm text-gray-600">Всего</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ activeCertificatesCount }}</div>
                                    <div class="text-sm text-gray-600">Активных</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ locationsCount }}</div>
                                    <div class="text-sm text-gray-600">Локаций</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-yellow-600">{{ certificatesLimit }}</div>
                                    <div class="text-sm text-gray-600">Лимит</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
