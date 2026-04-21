<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Layout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    certificates: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');
const sort = ref(props.filters.sort || 'default');
const categories = ref(props.filters.categories || []);

const CATEGORY_OPTIONS = [
    { id: 'horeca', label: 'HoReCa' },
    { id: 'retail', label: 'Розничная торговля' },
    { id: 'services', label: 'Сфера услуг' },
    { id: 'sport', label: 'Активный отдых и спорт' },
    { id: 'entertainment', label: 'Впечатления и развлечения' },
    { id: 'education', label: 'Обучение и дети' },
];

const applyFilters = () => {
    router.get(route('client.certificates.index'), {
        search: search.value,
        min_price: minPrice.value,
        max_price: maxPrice.value,
        categories: categories.value,
        sort: sort.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleCategory = (id) => {
    const s = new Set(categories.value || []);
    if (s.has(id)) s.delete(id);
    else s.add(id);
    categories.value = Array.from(s);
};

const resetFilters = () => {
    search.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    categories.value = [];
    sort.value = 'default';
    applyFilters();
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
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Сортировка</label>
                            <select v-model="sort"
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                <option value="default">По умолчанию (Pro → Start → Free)</option>
                                <option value="price_asc">По цене (возрастание)</option>
                                <option value="price_desc">По цене (убывание)</option>
                                <option value="popularity">По популярности (30 дней)</option>
                                <option value="newest">По новизне</option>
                            </select>
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
                        <div class="flex items-end gap-2">
                            <button @click="applyFilters"
                                    class="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white px-4 py-2 rounded-lg hover:from-purple-700 hover:to-pink-600 transition">
                                Применить
                            </button>
                            <button @click="resetFilters"
                                    class="w-full border px-4 py-2 rounded-lg hover:bg-gray-50 transition">
                                Сбросить
                            </button>
                        </div>
                    </div>

                    <div class="mt-5">
                        <div class="text-sm font-medium text-gray-700 mb-2">Категории</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                            <label v-for="c in CATEGORY_OPTIONS" :key="c.id"
                                   class="flex items-center gap-2 rounded-lg border px-3 py-2 cursor-pointer hover:bg-gray-50">
                                <input type="checkbox"
                                       :checked="(categories || []).includes(c.id)"
                                       @change="() => toggleCategory(c.id)" />
                                <span class="text-sm text-gray-700">{{ c.label }}</span>
                            </label>
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
                                    <p class="text-sm text-gray-500 mt-1">{{ certificate.organization.name }}</p>
                                </div>
                                <div class="text-2xl font-bold text-purple-600">
                                    {{ certificate.amount }} {{ certificate.currency || 'BYN' }}
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
                                    Категория: {{ certificate.category }}
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
