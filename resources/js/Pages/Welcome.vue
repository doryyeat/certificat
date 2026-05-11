<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    clientType: String,
});

// Состояние для модалки выбора типа
const showTypeModal = ref(!props.clientType);
const selectedType = ref(props.clientType || 'client');

// Плавающие элементы для фона
const floatingElements = ref(Array.from({ length: 20 }, (_, i) => ({
    id: i,
    size: Math.random() * 100 + 50,
    left: Math.random() * 100,
    top: Math.random() * 100,
    duration: Math.random() * 20 + 10,
    delay: Math.random() * 5,
})));

// Контент в зависимости от типа пользователя
const heroContent = computed(() => {
    if (selectedType.value === 'business') {
        return {
            badge: '🚀 Для бизнеса · Увеличьте продажи',
            title: 'Платформа для управления',
            titleGradient: 'подарочными сертификатами',
            description: 'Создавайте шаблоны подарочных сертификатов, ведите продажи через каталог и заказы, гасите номинал в точках продаж и смотрите отчёты — всё в одном кабинете организации.',
            cta: 'Подать заявку',
            ctaLink: route('business.apply'),
            features: [
                { icon: '📊', text: 'Аналитика продаж' },
                { icon: '🎫', text: 'Учёт номинала и погашений' },
                { icon: '📍', text: 'Точки продаж и геоданные' },
                { icon: '💳', text: 'Онлайн-оплата и статусы заказов' },
            ],
            stats: [
                { value: '1000', label: 'Сертификатов' },
                { value: '200', label: 'Бизнесов' },
                { value: '98%', label: 'Довольных' },
            ]
        };
    } else {
        return {
            badge: '✨ Для покупателей · Дарите эмоции',
            title: 'Подарочные',
            titleGradient: 'сертификаты',
            description: 'Тысячи сертификатов от лучших заведений. Рестораны, кафе, спа-салоны, магазины и многое другое в одном месте.',
            cta: 'Выбрать сертификат',
            ctaLink: route('client.certificates.index'),
            features: [
                { icon: '🎁', text: 'Мгновенная доставка' },
                { icon: '💳', text: 'Удобная оплата' },
                { icon: '🔍', text: 'Каталог и фильтры по цене' },
                { icon: '📄', text: 'PDF и данные сертификата на почту' },
            ],
            stats: [
                { value: '1000', label: 'Сертификатов' },
                { value: '200', label: 'Бизнесов' },
                { value: '4.9', label: 'Рейтинг' },
            ]
        };
    }
});

// Функция установки типа пользователя
function setClientType(type) {
    axios.post(route('set-client-type'), { clientType: type })
        .then(() => {
            selectedType.value = type;
            showTypeModal.value = false;
            // Обновляем страницу для применения изменений
            window.location.reload();
        })
        .catch(error => {
            console.error('Error setting client type:', error);
        });
}

// Анимации при скролле
const observer = ref(null);

onMounted(() => {
    observer.value = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
    );

    document.querySelectorAll('.animate-on-scroll').forEach((el) => {
        observer.value.observe(el);
    });
});
</script>

<template>
    <Head title="GiftHub - Подарочные сертификаты" />

    <div class="min-h-screen bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 text-white overflow-hidden">
        <!-- Анимированный фон -->
        <div class="fixed inset-0 pointer-events-none">
            <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%239C92AC%22%20fill-opacity%3D%220.05%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E');"></div>

            <div v-for="item in floatingElements" :key="item.id"
                 class="absolute rounded-full bg-white/5 backdrop-blur-sm"
                 :style="{
                    width: item.size + 'px',
                    height: item.size + 'px',
                    left: item.left + '%',
                    top: item.top + '%',
                    animation: `float ${item.duration}s ease-in-out ${item.delay}s infinite`
                }">
            </div>
        </div>

        <!-- Основной контент -->
        <div class="relative z-10">
            <div
                v-if="$page.props.flash?.message"
                class="container mx-auto px-6 pt-4"
            >
                <div
                    class="rounded-lg border border-emerald-400/30 bg-emerald-500/20 px-4 py-3 text-sm text-emerald-100"
                >
                    {{ $page.props.flash.message }}
                </div>
            </div>
            <!-- Навигация -->

            <header>
            <nav class="container mx-auto px-6 py-8">
                <div class="flex items-center justify-between">
                    <!-- Логотип -->
                    <Link href="/" class="flex items-center space-x-3 group">
                        <div class="relative">
                            <div class="w-12 h-12 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-2xl rotate-3 group-hover:rotate-6 transition-transform duration-300"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-2xl font-bold text-white drop-shadow-lg">🎁</span>
                            </div>
                        </div>
                        <span class="text-2xl font-bold bg-gradient-to-r from-pink-300 to-yellow-300 bg-clip-text text-transparent">
                            GiftHub
                        </span>
                    </Link>

                    <!-- Переключатель типа -->
                    <button @click="showTypeModal = true"
                            class="hidden md:flex items-center space-x-2 px-4 py-2 bg-white/10 rounded-full hover:bg-white/20 transition-all duration-300">
                        <span v-if="selectedType === 'business'" class="text-sm">🏢 Бизнес</span>
                        <span v-else class="text-sm">👤 Покупатель</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Навигационные ссылки -->
                    <div class="flex items-center space-x-8">
                        <template v-if="$page.props.auth?.user">
                            <!-- Для авторизованных пользователей показываем ссылки в зависимости от типа -->
                            <Link v-if="$page.props.auth.user.client_type === 'business'"
                                  :href="route('certificates.index')"
                                  class="relative group px-6 py-2 overflow-hidden rounded-full bg-white/10 hover:bg-white/20 transition-all duration-300">
                                <span class="relative z-10">Кабинет</span>
                            </Link>
                            <Link v-else-if="$page.props.auth.user.client_type === 'client'"
                                  :href="route('client.my-certificates')"
                                  class="relative group px-6 py-2 overflow-hidden rounded-full bg-white/10 hover:bg-white/20 transition-all duration-300">
                                <span class="relative z-10">Мои сертификаты</span>
                            </Link>
                            <Link v-else
                                  :href="route('profile.edit')"
                                  class="relative group px-6 py-2 overflow-hidden rounded-full bg-white/10 hover:bg-white/20 transition-all duration-300">
                                <span class="relative z-10">Профиль</span>
                            </Link>
                        </template>

                        <template v-else>
                            <Link v-if="canLogin" :href="route('login')"
                                  class="relative group px-6 py-2 overflow-hidden rounded-full hover:bg-white/10 transition-all duration-300">
                                <span class="relative z-10">Вход</span>
                            </Link>

                            <Link  v-if="canRegister && selectedType === 'client'" :href="route('register')"
                                  class="relative group px-8 py-3 overflow-hidden rounded-full bg-gradient-to-r from-pink-500 to-yellow-500 hover:from-pink-600 hover:to-yellow-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <span class="relative z-10 font-semibold">Регистрация</span>
                                <div class="absolute inset-0 bg-white/20 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
                            </Link>
                            <Link v-else
                                :href="route('business.apply')"
                                  class="relative group px-8 py-3 overflow-hidden rounded-full bg-gradient-to-r from-pink-500 to-yellow-500 hover:from-pink-600 hover:to-yellow-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">

                                <span class="relative z-10 font-semibold">Регистрация</span>
                                <div class="absolute inset-0 bg-white/20 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
                            </Link>
                        </template>
                    </div>
                </div>
            </nav>
            </header>
            <!-- Hero секция -->
            <section class="container mx-auto px-6 pt-20 pb-32">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Левая колонка -->
                    <div class="space-y-8 animate-on-scroll">
                        <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            <span class="text-sm font-medium">{{ heroContent.badge }}</span>
                        </div>

                        <h1 class="text-5xl lg:text-7xl font-bold leading-tight">
                            <span class="bg-gradient-to-r from-pink-300 via-yellow-300 to-green-300 bg-clip-text text-transparent">
                                {{ heroContent.title }}
                            </span>
                            <br />
                            <span class="relative inline-block group">
                                {{ heroContent.titleGradient }}
                                <span class="absolute -bottom-2 left-0 w-full h-1 bg-gradient-to-r from-pink-500 via-yellow-500 to-green-500 rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-700"></span>
                            </span>
                        </h1>

                        <p class="text-xl text-gray-300 leading-relaxed">
                            {{ heroContent.description }}
                        </p>

                        <div class="flex flex-wrap gap-6">
                            <Link :href="heroContent.ctaLink"
                                  class="group relative px-10 py-5 overflow-hidden rounded-2xl bg-gradient-to-r from-pink-500 to-yellow-500 hover:from-pink-600 hover:to-yellow-600 transition-all duration-300 shadow-2xl hover:shadow-3xl transform hover:-translate-y-1">
                                <span class="relative z-10 text-lg font-semibold">{{ heroContent.cta }} →</span>
                                <div class="absolute inset-0 bg-white/20 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-700 skew-x-12"></div>
                            </Link>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-8">
                            <div v-for="feature in heroContent.features" :key="feature.icon"
                                 class="flex items-center space-x-2">
                                <span class="text-2xl">{{ feature.icon }}</span>
                                <span class="text-sm text-gray-300">{{ feature.text }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Правая колонка -->
                    <div class="relative animate-on-scroll">
                        <div class="absolute inset-0 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-3xl blur-3xl opacity-30 animate-pulse"></div>

                        <div class="relative bg-white/10 backdrop-blur-xl rounded-3xl p-8 border border-white/20 transform hover:scale-105 transition-all duration-500 hover:rotate-1">
                            <div class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-full opacity-20 animate-ping"></div>

                            <div class="relative z-10">
                                <div class="flex items-center justify-between mb-8">
                                    <h3 class="text-2xl font-bold">
                                        {{ selectedType === 'business' ? 'Кабинет организации' : 'Каталог сертификатов' }}
                                    </h3>
                                    <div class="flex -space-x-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-pink-500 to-yellow-500 border-2 border-white"></div>
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-yellow-500 to-green-500 border-2 border-white"></div>
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-blue-500 border-2 border-white"></div>
                                    </div>
                                </div>

                                <template v-if="selectedType === 'business'">
                                    <div class="space-y-6">
                                        <p class="text-sm text-gray-300 leading-relaxed">
                                            Пример экрана: шаблоны сертификатов, активные позиции в каталоге, заказы и погашения в точках продаж — данные агрегируются для отчётов и тарифа.
                                        </p>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-white/5 rounded-xl p-4">
                                                <div class="text-sm text-gray-400">В каталоге</div>
                                                <div class="text-3xl font-bold text-yellow-400">24</div>
                                            </div>
                                            <div class="bg-white/5 rounded-xl p-4">
                                                <div class="text-sm text-gray-400">Точек продаж</div>
                                                <div class="text-3xl font-bold text-green-400">6</div>
                                            </div>
                                        </div>

                                        <div class="bg-white/5 rounded-xl p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-medium">Погашено за месяц</span>
                                                <span class="text-xs text-green-400">по данным аналитики</span>
                                            </div>
                                            <div class="w-full bg-white/10 rounded-full h-2">
                                                <div class="bg-gradient-to-r from-pink-500 to-yellow-500 h-2 rounded-full" style="width: 58%"></div>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-400 border-t border-white/10 pt-4">
                                            Демо-виджет: POS-гашение, менеджеры и брендирование PDF доступны после подключения организации.
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <p class="text-sm text-gray-300 mb-6 leading-relaxed">
                                        Вы выбираете сертификат у организации из каталога, оплачиваете заказ онлайн и получаете подтверждение; купленные сертификаты и PDF доступны в личном кабинете покупателя.
                                    </p>
                                    <div class="grid grid-cols-2 gap-4 mb-6">
                                        <div class="space-y-2">
                                            <div class="w-full aspect-square bg-white/5 rounded-2xl flex items-center justify-center">
                                                <div class="grid grid-cols-3 gap-1 p-2">
                                                    <div v-for="i in 9" :key="i"
                                                         class="w-2 h-2 bg-white/30 rounded"
                                                         :class="{ 'opacity-0': i % 2 === 0 }">
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-xs text-center text-gray-400">Превью карточки в каталоге</p>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="bg-white/5 rounded-xl p-4">
                                                <div class="text-sm text-gray-400">Номинал к оплате</div>
                                                <div class="text-3xl font-bold text-yellow-400">
                                                    100 BYN
                                                </div>
                                            </div>

                                            <div class="bg-white/5 rounded-xl p-4">
                                                <div class="text-sm text-gray-400">Статус заказа</div>
                                                <div class="text-lg font-bold text-green-400">Оплачен</div>
                                                <div class="text-xs text-gray-400 mt-2">Переход к оплате из карточки сертификата</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white/5 rounded-xl p-4">
                                        <div class="text-sm font-medium mb-2">Мои сертификаты</div>
                                        <div class="flex items-center gap-2 text-xs text-gray-400">
                                            <span class="rounded bg-white/10 px-2 py-1">PDF</span>
                                            <span class="rounded bg-white/10 px-2 py-1">История операций</span>
                                            <span class="rounded bg-white/10 px-2 py-1">Профиль</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Статистика -->
            <section class="container mx-auto px-6 py-12">
                <div class="grid grid-cols-3 gap-8 max-w-3xl mx-auto">
                    <div v-for="stat in heroContent.stats" :key="stat.label"
                         class="text-center group cursor-pointer">
                        <div class="text-4xl font-bold bg-gradient-to-r from-pink-400 to-yellow-400 bg-clip-text text-transparent group-hover:scale-110 transition-transform">
                            {{ stat.value }}
                        </div>
                        <div class="text-sm text-gray-400 mt-2">{{ stat.label }}</div>
                    </div>
                </div>
            </section>

            <!-- Секция с преимуществами -->
            <section class="container mx-auto px-6 py-24">
                <h2 class="text-4xl font-bold text-center mb-16 animate-on-scroll">
                    <span class="bg-gradient-to-r from-pink-300 to-yellow-300 bg-clip-text text-transparent">
                        {{ selectedType === 'business' ? 'Возможности для бизнеса' : 'Почему выбирают нас' }}
                    </span>
                </h2>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <template v-if="selectedType === 'business'">
                        <div v-for="(feature, index) in [
                            { icon: '💳', title: 'Тарифы', desc: 'Тарифный план с лимитами на сертификаты, аналитику и функции кабинета — смена тарифа в разделе «Тариф».' },
                            { icon: '📊', title: 'Аналитика', desc: 'Детальная статистика продаж' },
                            { icon: '🎨', title: 'Шаблоны', desc: 'Готовые дизайны сертификатов' },
                                                   ]" :key="index" class="group relative bg-white/5 backdrop-blur-sm rounded-2xl p-8 hover:bg-white/10 transition-all duration-500 transform hover:-translate-y-2 animate-on-scroll">
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-2xl mb-6 flex items-center justify-center text-3xl transform group-hover:rotate-6 transition-transform duration-300">
                                    {{ feature.icon }}
                                </div>
                                <h3 class="text-xl font-bold mb-4">{{ feature.title }}</h3>
                                <p class="text-gray-400">{{ feature.desc }}</p>
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        <div v-for="(feature, index) in [
                            { icon: '🎨', title: 'Большой выбор', desc: 'Тысячи сертификатов на любой вкус' },
                            { icon: '⚡', title: 'Мгновенно', desc: 'Получите PDF на email сразу' },
                            { icon: '🛡️', title: 'Прозрачные условия', desc: 'Срок действия и правила использования указаны в карточке сертификата до покупки.' },
                        ]" :key="index" class="group relative bg-white/5 backdrop-blur-sm rounded-2xl p-8 hover:bg-white/10 transition-all duration-500 transform hover:-translate-y-2 animate-on-scroll">
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-2xl mb-6 flex items-center justify-center text-3xl transform group-hover:rotate-6 transition-transform duration-300">
                                    {{ feature.icon }}
                                </div>
                                <h3 class="text-xl font-bold mb-4">{{ feature.title }}</h3>
                                <p class="text-gray-400">{{ feature.desc }}</p>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <!-- CTA секция -->
            <section class="container mx-auto px-6 py-24">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 p-16 text-center animate-on-scroll">
                    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.4%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E');"></div>

                    <div class="relative z-10">
                        <h2 class="text-4xl font-bold mb-6">
                            {{ selectedType === 'business' ? 'Готовы развивать бизнес?' : 'Готовы дарить подарки?' }}
                        </h2>
                        <p class="text-xl mb-10 max-w-2xl mx-auto opacity-90">
                            {{ selectedType === 'business'
                            ? 'Присоединяйтесь к тысячам бизнесов, которые уже используют GiftHub'
                            : 'Найдите идеальный подарок для близких и друзей' }}
                        </p>

                        <Link :href="selectedType === 'business' ? route('business.apply') : route('client.certificates.index')"
                              class="inline-flex items-center space-x-3 bg-white text-purple-600 px-10 py-5 rounded-2xl font-bold text-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl group">
                            <span>{{ selectedType === 'business' ? 'Подать заявку' : 'Выбрать сертификат' }}</span>
                            <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </Link>
                    </div>

                    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2 animate-pulse"></div>
                    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/10 rounded-full translate-x-1/2 translate-y-1/2 animate-pulse delay-1000"></div>
                </div>
            </section>

            <!-- Модалка выбора типа -->
            <div v-if="showTypeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-8 rounded-2xl shadow-2xl w-[400px] text-center relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-full opacity-20"></div>

                    <div class="relative z-10">
                        <h2 class="text-3xl font-bold mb-2 text-gray-800">Добро пожаловать! 👋</h2>
                        <p class="text-gray-600 mb-8">Выберите, как вы хотите использовать платформу</p>

                        <div class="grid grid-cols-2 gap-4">
                            <button @click="setClientType('client')"
                                    class="group p-6 rounded-xl border-2 border-blue-200 hover:border-blue-500 transition-all duration-300 hover:shadow-lg">
                                <span class="text-4xl mb-3 block group-hover:scale-110 transition-transform">👤</span>
                                <span class="font-semibold text-gray-800">Покупатель</span>
                                <p class="text-xs text-gray-500 mt-2">Хочу покупать сертификаты</p>
                            </button>

                            <button @click="setClientType('business')"
                                    class="group p-6 rounded-xl border-2 border-purple-200 hover:border-purple-500 transition-all duration-300 hover:shadow-lg">
                                <span class="text-4xl mb-3 block group-hover:scale-110 transition-transform">🏢</span>
                                <span class="font-semibold text-gray-800">Бизнес</span>
                                <p class="text-xs text-gray-500 mt-2">Хочу продавать сертификаты</p>
                            </button>
                        </div>

                        <button @click="setClientType('client')"
                                class="mt-6 text-sm text-gray-400 hover:text-gray-600 transition-colors">
                            Пропустить →
                        </button>
                    </div>
                </div>
            </div>

            <!-- Футер -->
            <footer class="container mx-auto px-6 py-12 border-t border-white/10">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center space-x-3 mb-4 md:mb-0">
                        <div class="w-10 h-10 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-xl rotate-3 flex items-center justify-center text-xl">
                            🎁
                        </div>
                        <span class="text-xl font-bold">GiftHub</span>
                    </div>

                    <div class="text-sm text-gray-400">
                        Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                    </div>

                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879v-6.99h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.99C18.343 21.128 22 16.991 22 12z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style>
@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-on-scroll.animate-in {
    opacity: 1;
    transform: translateY(0);
}

.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}
</style>
