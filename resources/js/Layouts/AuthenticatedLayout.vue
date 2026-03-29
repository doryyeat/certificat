<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const isScrolled = ref(false);

// Отслеживаем скролл для изменения стилей навбара
if (typeof window !== 'undefined') {
    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 20;
    });
}
</script>

<template>
    <div>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
            <!-- Навбар с современным дизайном -->
            <nav
                class="fixed top-0 left-0 right-0 z-50 transition-all duration-500"
                :class="[
                    isScrolled
                        ? 'bg-white/80 backdrop-blur-xl shadow-lg py-2'
                        : 'bg-transparent py-4'
                ]"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between items-center">
                        <!-- Левая часть с логотипом -->
                        <div class="flex items-center">
                            <!-- Логотип с анимацией -->
                            <Link :href="route('business.dashboard')" class="group flex items-center space-x-3">
                                <div class="relative">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-500 rounded-xl rotate-3 group-hover:rotate-6 transition-all duration-300 shadow-lg"></div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-xl font-bold text-white drop-shadow-md">GB</span>
                                    </div>
                                </div>
                                <span class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-500 bg-clip-text text-transparent hidden sm:block">
                                    GiftHub Business
                                </span>
                            </Link>

                            <!-- Навигационные ссылки с красивым оформлением -->
                            <div class="hidden lg:flex lg:ml-12 lg:space-x-1">
                                <NavLink
                                    :href="route('business.dashboard')"
                                    :active="route().current('business.dashboard')"
                                    class="relative px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300"
                                    :class="[
                                        route().current('business.dashboard')
                                            ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-md'
                                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/50'
                                    ]"
                                >
                                    <span class="relative z-10">📊 Дашборд</span>
                                </NavLink>

                                <NavLink
                                    :href="route('business.certificates.index')"
                                    :active="route().current('business.certificates.*')"
                                    class="relative px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300"
                                    :class="[
                                        route().current('business.certificates.*')
                                            ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-md'
                                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/50'
                                    ]"
                                >
                                    <span class="relative z-10">🎫 Сертификаты</span>
                                </NavLink>

                                <NavLink
                                    :href="route('business.customers.index')"
                                    :active="route().current('customers.*')"
                                    class="relative px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300"
                                    :class="[
                                        route().current('business.customers.*')
                                            ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-md'
                                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/50'
                                    ]"
                                >
                                    <span class="relative z-10">👥 Клиенты</span>
                                </NavLink>

                                <NavLink
                                    :href="route('business.products.index')"
                                    :active="route().current('business.products.*')"
                                    class="relative px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300"
                                    :class="[
                                        route().current('business.products.*')
                                            ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-md'
                                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/50'
                                    ]"
                                >
                                    <span class="relative z-10">📦 Товары</span>
                                </NavLink>

                                <NavLink
                                    :href="route('business.orders.index')"
                                    :active="route().current('business.orders.*')"
                                    class="relative px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300"
                                    :class="[
                                        route().current('business.orders.*')
                                            ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-md'
                                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/50'
                                    ]"
                                >
                                    <span class="relative z-10">🛒 Заказы</span>
                                </NavLink>

                                <NavLink
                                    :href="route('business.profile.show')"
                                    :active="route().current('business.profile.*')"
                                    class="relative px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300"
                                    :class="[
                                        route().current('business.profile.*')
                                            ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-md'
                                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/50'
                                    ]"
                                >
                                    <span class="relative z-10">🏢 Профиль</span>
                                </NavLink>
                            </div>
                        </div>

                        <!-- Правая часть с профилем -->
                        <div class="flex items-center space-x-4">
                            <!-- Уведомления -->
                            <button class="relative p-2 text-gray-500 hover:text-gray-700 transition-colors">
                                <span class="absolute -top-1 -right-1 w-4 h-4 bg-gradient-to-r from-purple-600 to-pink-500 rounded-full text-[10px] text-white flex items-center justify-center animate-pulse">
                                    3
                                </span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </button>

                            <!-- Профиль с красивым дропдауном -->
                            <div class="relative">
                                <Dropdown align="right" width="64">
                                    <template #trigger>
                                        <button class="group flex items-center space-x-3 p-1 rounded-full hover:bg-gray-100/50 transition-all duration-300">
                                            <!-- Аватар с градиентом -->
                                            <div class="relative">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 p-0.5">
                                                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center">
                                                        <span class="text-sm font-bold bg-gradient-to-r from-purple-600 to-pink-500 bg-clip-text text-transparent">
                                                            {{ $page.props.auth.user.name.charAt(0) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                                            </div>

                                            <!-- Имя пользователя и стрелка -->
                                            <div class="hidden md:block text-left">
                                                <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                                    {{ $page.props.auth.user.name }}
                                                </p>
                                                <p class="text-xs text-gray-400">
                                                    {{ $page.props.auth.user.email }}
                                                </p>
                                            </div>

                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="py-2">
                                            <!-- Шапка профиля в дропдауне -->
                                            <div class="px-4 py-3 border-b border-gray-100">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $page.props.auth.user.name }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    {{ $page.props.auth.user.email }}
                                                </p>
                                            </div>

                                            <!-- Меню профиля -->
                                            <DropdownLink :href="route('business.profile.show')" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span>Профиль компании</span>
                                            </DropdownLink>

                                            <div class="border-t border-gray-100 my-2"></div>

                                            <!-- Кнопка выхода -->
                                            <DropdownLink :href="route('logout')" method="post" as="button" class="flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-orange-50 w-full text-left">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                </svg>
                                                <span>Выйти</span>
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Мобильное меню кнопка -->
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="lg:hidden p-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 transition-all duration-300"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Мобильное меню -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="lg:hidden absolute top-full left-0 right-0 bg-white/95 backdrop-blur-xl border-t border-gray-100 shadow-xl animate-slideDown"
                >
                    <div class="px-4 py-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('business.dashboard')"
                            :active="route().current('business.dashboard')"
                            class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
                            :class="[
                                route().current('business.dashboard')
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <span>📊</span>
                            <span>Дашборд</span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('business.certificates.index')"
                            :active="route().current('business.certificates.*')"
                            class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
                            :class="[
                                route().current('business.certificates.*')
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <span>🎫</span>
                            <span>Сертификаты</span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('business.customers.index')"
                            :active="route().current('business.customers.*')"
                            class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
                            :class="[
                                route().current('business.customers.*')
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <span>👥</span>
                            <span>Клиенты</span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('business.products.index')"
                            :active="route().current('business.products.*')"
                            class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
                            :class="[
                                route().current('business.products.*')
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <span>📦</span>
                            <span>Товары</span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('business.orders.index')"
                            :active="route().current('business.orders.*')"
                            class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
                            :class="[
                                route().current('orders.*')
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <span>🛒</span>
                            <span>Заказы</span>
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('business.profile.show')"
                            :active="route().current('business.profile.*')"
                            class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300"
                            :class="[
                                route().current('business.profile.*')
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <span>🏢</span>
                            <span>Профиль</span>
                        </ResponsiveNavLink>

                        <!-- Кнопка выхода в мобильном меню -->
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                                           class="flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 w-full text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Выйти</span>
                        </ResponsiveNavLink>
                    </div>

                    <!-- Информация о пользователе в мобильном меню -->
                    <div class="border-t border-gray-100 px-4 py-4 bg-gray-50/50">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 p-0.5">
                                <div class="w-full h-full rounded-full bg-white flex items-center justify-center">
                                    <span class="text-sm font-bold bg-gradient-to-r from-purple-600 to-pink-500 bg-clip-text text-transparent">
                                        {{ $page.props.auth.user.name.charAt(0) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $page.props.auth.user.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Отступ для контента из-за фиксированного навбара -->
            <div class="pt-24">
                <!-- Page Heading -->
                <header v-if="$slots.header" class="mb-8">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <slot name="header" />
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>

<style>
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slideDown {
    animation: slideDown 0.3s ease-out;
}
</style>
