<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    user: Object,
});
</script>

<template>
    <Head title="Мой профиль" />

    <ClientLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Шапка -->
                    <div class="h-32 bg-gradient-to-r from-purple-600 to-pink-500"></div>

                    <!-- Аватар -->
                    <div class="px-6 pb-6 -mt-12">
                        <div class="flex justify-between items-end">
                            <div class="flex items-end space-x-6">
                                <div class="w-24 h-24 rounded-full bg-white p-1 shadow-lg">
                                    <div class="w-full h-full rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center text-4xl text-white">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <h1 class="text-2xl font-bold">{{ user.name }}</h1>
                                    <p class="text-gray-500">{{ user.email }}</p>
                                </div>
                            </div>
                            <Link :href="route('client.profile.edit')"
                                  class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-4 py-2 rounded-lg hover:from-purple-700 hover:to-pink-600 transition text-sm">
                                Редактировать
                            </Link>
                        </div>
                    </div>

                    <!-- Информация -->
                    <div class="border-t px-6 py-6">
                        <h3 class="text-lg font-semibold mb-4">Личная информация</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm text-gray-500">Имя</div>
                                <div class="font-medium">{{ user.name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Email</div>
                                <div class="font-medium">{{ user.email }}</div>
                            </div>
                            <div v-if="user.phone">
                                <div class="text-sm text-gray-500">Телефон</div>
                                <div class="font-medium">{{ user.phone }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Тип аккаунта</div>
                                <div class="font-medium">Покупатель</div>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика -->
                    <div class="border-t px-6 py-6 bg-gray-50">
                        <h3 class="text-lg font-semibold mb-4">Статистика</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">0</div>
                                <div class="text-sm text-gray-600">Сертификатов</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">0</div>
                                <div class="text-sm text-gray-600">Заказов</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">0 BYN</div>
                                <div class="text-sm text-gray-600">Потрачено</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
