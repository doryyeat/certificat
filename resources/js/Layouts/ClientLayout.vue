<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { ref } from 'vue';

const showingMobileMenu = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Навигация для клиентов -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Логотип -->
                        <Link :href="route('client.certificates.index')" class="flex items-center">
                            <div class="relative">
                                <div class="w-12 h-12 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-2xl rotate-3 group-hover:rotate-6 transition-transform duration-300"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-white drop-shadow-lg">🎁</span>
                                </div>
                            </div>
                            <span class="ml-2 text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-500 bg-clip-text text-transparent">
                                GiftHub
                            </span>
                        </Link>

                        <!-- Навигация (десктоп) -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link :href="route('client.certificates.index')"
                                  class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                                  :class="route().current('client.certificates.index') ? 'border-purple-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Каталог
                            </Link>

                            <Link v-if="$page.props.auth.user" :href="route('client.my-certificates')"
                                  class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                                  :class="route().current('client.my-certificates') ? 'border-purple-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Мои сертификаты
                            </Link>
                            <Link v-if="$page.props.auth.user" :href="route('client.profile.show')"
                                  class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                                  :class="route().current('client.profile.*') ? 'border-purple-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Мой профиль
                            </Link>
                        </div>
                    </div>

                    <!-- Профиль и выход (десктоп) -->
                    <div v-if="$page.props.auth.user" class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                        <Link :href="route('client.profile.show')" class="flex items-center space-x-3 text-gray-700 hover:text-gray-900">
                            <span>{{ $page.props.auth.user.name }}</span>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center text-white">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                        </Link>

                        <!-- Кнопка выхода -->
                        <Link :href="route('logout')" method="post" as="button"
                              class="flex items-center space-x-2 px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Выйти</span>
                        </Link>
                    </div>

                    <!-- Мобильное меню кнопка -->
                    <div class="flex items-center sm:hidden">
                        <button @click="showingMobileMenu = !showingMobileMenu"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': showingMobileMenu, 'inline-flex': !showingMobileMenu }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': !showingMobileMenu, 'inline-flex': showingMobileMenu }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Мобильное меню -->
            <div :class="{ 'block': showingMobileMenu, 'hidden': !showingMobileMenu }" class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="route('client.certificates.index')"
                          class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
                          :class="route().current('client.certificates.index') ? 'border-purple-500 text-purple-700 bg-purple-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'">
                        Каталог
                    </Link>
                    <Link :href="route('client.my-certificates')"
                          class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
                          :class="route().current('client.my-certificates') ? 'border-purple-500 text-purple-700 bg-purple-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'">
                        Мои сертификаты
                    </Link>
                    <Link :href="route('client.profile.show')"
                          class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
                          :class="route().current('client.profile.*') ? 'border-purple-500 text-purple-700 bg-purple-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'">
                        Мой профиль
                    </Link>
                </div>

                <!-- Информация о пользователе в мобильном меню -->

                <div v-if="$page.props.auth.user" class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center text-white">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-base text-gray-800">{{ $page.props.auth.user.name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <!-- Кнопка выхода в мобильном меню -->
                        <Link :href="route('logout')" method="post" as="button"
                              class="block w-full text-left px-4 py-2 text-base font-medium text-red-600 hover:text-red-700 hover:bg-red-50">
                            Выйти
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Основной контент -->
        <main>
            <slot />
        </main>
    </div>
</template>
