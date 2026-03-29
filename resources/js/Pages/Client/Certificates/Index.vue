<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Layout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    certificates: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');

const applyFilters = () => {
    router.get(route('client.certificates.index'), {
        search: search.value,
        min_price: minPrice.value,
        max_price: maxPrice.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Подарочные сертификаты" />

    <Layout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Подарочные сертификаты</h1>
                    <p class="mt-2 text-gray-600">Выберите идеальный подарок для близких</p>
                </div>

                <!-- Фильтры -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Название заведения..."
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Мин. цена</label>
                            <input
                                v-model="minPrice"
                                type="number"
                                placeholder="От"
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Макс. цена</label>
                            <input
                                v-model="maxPrice"
                                type="number"
                                placeholder="До"
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="applyFilters"
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white px-4 py-2 rounded-lg hover:from-purple-700 hover:to-pink-600 transition"
                            >
                                Применить
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Список сертификатов -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="certificate in certificates.data" :key="certificate.id"
                         class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition group">
                        <div class="h-48 bg-gradient-to-r from-purple-600 to-pink-500 relative">
                            <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-20 transition"></div>
                            <div class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm rounded-full px-3 py-1 text-white text-sm">
                                {{ certificate.organization.name }}
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ certificate.template?.name || 'Подарочный сертификат' }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">Код: {{ certificate.code }}</p>
                                </div>
                                <div class="text-2xl font-bold text-purple-600">
                                    {{ certificate.amount }} ₽
                                </div>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Действует до: {{ new Date(certificate.expires_at).toLocaleDateString() }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Получатель: {{ certificate.recipient_name || 'Любой' }}
                                </div>
                            </div>

                            <Link v-if="$page.props.auth.user" :href="route('client.certificates.show', certificate.id)"
                                  class="block w-full text-center bg-gradient-to-r from-purple-600 to-pink-500 text-white px-4 py-3 rounded-lg hover:from-purple-700 hover:to-pink-600 transition font-medium">
                                Купить
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Пагинация -->
                <div class="mt-8">
                    <Link v-for="link in certificates.links" :key="link.label"
                          :href="link.url || '#'"
                          v-html="link.label"
                          class="px-3 py-2 mx-1 rounded"
                          :class="{
                              'bg-purple-600 text-white': link.active,
                              'bg-white text-gray-700 hover:bg-gray-50': !link.active && link.url,
                              'text-gray-400 cursor-not-allowed': !link.url
                          }"
                    />
                </div>
            </div>
        </div>
    </Layout>
</template>
