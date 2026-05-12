<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    Точки продаж
                </h2>
                <button
                    @click="openAddModal"
                    class="rounded-md bg-gradient-to-r from-pink-500 to-yellow-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-pink-600 hover:to-yellow-600 transition-all duration-300"
                >
                    + Добавить точку
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Карта -->
                <div class="mb-6 rounded-lg bg-white shadow overflow-hidden">
                    <div style="height: 500px; width: 100%; position: relative;">
                        <div class="absolute top-4 right-4 z-10 bg-white px-4 py-2 rounded-lg shadow-lg">
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" v-model="showMapMarkers" class="rounded border-gray-300">
                                <span>Показывать точки на карте</span>
                            </label>
                        </div>

                        <div v-if="geocoding" class="absolute top-4 left-4 z-10 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg">
                            <div class="flex items-center space-x-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Поиск адреса...</span>
                            </div>
                        </div>

                        <LMap
                            ref="map"
                            :zoom="zoom"
                            :center="center"
                            :use-global-leaflet="false"
                            @ready="onMapReady"
                            @click="onMapClick"
                        >
                            <LTileLayer
                                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                attribution="&copy; OpenStreetMap contributors"
                            />

                            <LMarker
                                v-for="store in stores.data"
                                :key="store.id"
                                :lat-lng="[store.lat, store.lng]"
                                @click="selectStore(store)"
                            >
                                <LPopup>
                                    <div class="p-3 min-w-[250px]">
                                        <div v-if="store.images && store.images[0]" class="mb-2">
                                            <img  v-if="getMainImage(store)"
                                                  :src="getMainImage(store).big_uri"
                                                  :alt="store.name" class="w-full h-32 object-cover rounded-lg"
                                                  @click="enlargeImage(getMainImage(store).big_uri, store.name)"
                                            >
                                        </div>
                                        <h3 class="font-bold text-lg">{{ store.name }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ store.address }}</p>
                                        <p class="text-sm text-gray-500 mt-1">📞 {{ store.phone }}</p>
                                        <p class="text-sm text-gray-500">🕐 {{ store.opening_hours || '—' }}</p>
                                        <div class="flex space-x-2 mt-3">
                                            <button
                                                @click.stop="editStore(store)"
                                                class="px-3 py-1 bg-yellow-500 text-white rounded-md text-sm hover:bg-yellow-600"
                                            >
                                                Редактировать
                                            </button>
                                            <button
                                                @click.stop="deleteStore(store)"
                                                class="px-3 py-1 bg-red-500 text-white rounded-md text-sm hover:bg-red-600"
                                            >
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                </LPopup>
                            </LMarker>

                            <LMarker
                                v-if="currentMarkerLat && currentMarkerLng"
                                :lat-lng="[currentMarkerLat, currentMarkerLng]"
                                :icon="currentMarkerIcon"
                            >
                                <LPopup>
                                    <div class="p-2">
                                        <p class="text-sm font-medium text-center">
                                            {{ editingStore ? 'Редактируемая точка' : 'Новая точка' }}
                                        </p>
                                        <p class="text-xs text-gray-600 text-center mt-1">
                                            {{ currentMarkerLat.toFixed(6) }}, {{ currentMarkerLng.toFixed(6) }}
                                        </p>
                                    </div>
                                </LPopup>
                            </LMarker>
                        </LMap>
                    </div>
                </div>

                <!-- Таблица магазинов -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Фото</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Название</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Адрес</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Телефон</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Часы работы</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Действия</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            <tr v-for="store in stores.data" :key="store.id">
                                <td class="px-4 py-3">
                                    <div class="relative">
                                        <img
                                            v-if="getMainImage(store)"
                                            :src="getMainImage(store).big_uri"
                                            :alt="store.name"
                                            class="h-12 w-12 rounded-lg object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                            @click="enlargeImage(getMainImage(store).big_uri, store.name)"
                                        >
                                        <div v-else class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <span class="text-gray-400 text-xs">Нет фото</span>
                                        </div>
                                        <span v-if="store.images && store.images.length > 1"
                                              class="absolute -top-1 -right-1 bg-indigo-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                                {{ store.images.length }}
                                            </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ store.name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs">
                                    <div class="truncate">{{ store.address }}</div>
                                    <div v-if="store.lat && store.lng" class="text-xs text-gray-400 mt-1">
                                        {{ store.lat }}, {{ store.lng }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ store.phone }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ store.opening_hours || '—' }}
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <button
                                        @click="editStore(store)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        Изменить
                                    </button>
                                    <button
                                        @click="deleteStore(store)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!stores.data?.length">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Добавьте первую точку — она нужна для выпуска сертификатов.
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Пагинация -->
                <div v-if="stores.links?.length > 3" class="mt-4 flex flex-wrap gap-2 justify-center">
                    <button
                        v-for="link in stores.links"
                        :key="link.label"
                        v-show="link.url"
                        type="button"
                        class="rounded border px-3 py-1 text-sm transition-all"
                        :class="link.active ? 'border-indigo-500 bg-indigo-50 text-indigo-600' : 'border-gray-300 hover:bg-gray-50'"
                        @click="router.visit(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
        <!-- Модальное окно для увеличенного изображения -->
        <Transition name="modal">
            <div v-if="showImageModal" class="fixed inset-0 z-[10000] overflow-y-auto" @click="closeImageModal">
                <div class="fixed inset-0 bg-black bg-opacity-90 transition-opacity"></div>

                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="relative max-w-4xl w-full" @click.stop>
                        <!-- Кнопка закрытия -->
                        <button
                            @click="closeImageModal"
                            class="absolute -top-10 right-0 text-white hover:text-gray-300 text-3xl"
                        >
                            ✕
                        </button>

                        <!-- Изображение -->
                        <img
                            :src="enlargedImageUrl"
                            :alt="enlargedImageAlt"
                            class="w-full h-auto rounded-lg shadow-2xl"
                        >

                        <!-- Подпись -->
                        <p class="text-center text-white mt-4">{{ enlargedImageAlt }}</p>
                    </div>
                </div>
            </div>
        </Transition>
        <!-- Модальное окно добавления/редактирования -->
        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                        <form @submit.prevent="saveStore">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    {{ editingStore ? 'Редактировать' : 'Добавить' }} точку продаж
                                </h3>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Название *</label>
                                        <input
                                            type="text"
                                            v-model="storeForm.name"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Например: Центральный магазин"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Телефон *</label>
                                        <input
                                            type="tel"
                                            v-model="storeForm.phone"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="+375XXXXXXXXX"
                                        />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Адрес *
                                            <span class="text-xs text-gray-500 ml-2">(введите адрес и он автоматически определит координаты)</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                v-model="storeForm.address"
                                                @blur="geocodeAddress"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="Введите полный адрес, например: г. Минск, ул. Ленина, 10"
                                            />
                                            <div v-if="geocoding" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                                <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="geocodeError" class="mt-1 text-xs text-red-600">
                                            {{ geocodeError }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Широта</label>
                                        <input
                                            type="number"
                                            step="any"
                                            v-model="storeForm.lat"
                                            @input="updateMarkerFromCoords"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50"
                                            placeholder="Определится автоматически"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Долгота</label>
                                        <input
                                            type="number"
                                            step="any"
                                            v-model="storeForm.lng"
                                            @input="updateMarkerFromCoords"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50"
                                            placeholder="Определится автоматически"
                                        />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Часы работы</label>
                                        <input
                                            type="text"
                                            v-model="storeForm.opening_hours"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Пн-Пт: 9:00-20:00, Сб-Вс: 10:00-18:00"
                                        />
                                    </div>

                                    <!-- Мультизагрузка изображений -->
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Фотографии магазина
                                            <span class="text-xs text-gray-500 ml-2">(можно выбрать несколько файлов)</span>
                                        </label>

                                        <!-- Существующие изображения (при редактировании) -->
                                        <div v-if="existingImages.length > 0" class="mb-4">
                                            <label class="block text-xs font-medium text-gray-500 mb-2">Существующие фото:</label>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                                                <div v-for="(image, index) in existingImages" :key="image.id" class="relative group">
                                                    <img
                                                        :src="image.big_uri"
                                                        class="w-full h-24 object-cover rounded-lg border-2"
                                                        :class="{ 'border-indigo-500 ring-2 ring-indigo-300': storeForm.main_photo_id === image.id }"
                                                    >
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all rounded-lg flex items-center justify-center gap-1">
                                                        <button
                                                            type="button"
                                                            @click="setMainImage(image.id)"
                                                            class="hidden group-hover:flex bg-green-500 text-white p-1 rounded text-xs"
                                                            title="Сделать главным"
                                                        >
                                                            ★
                                                        </button>
                                                        <button
                                                            type="button"
                                                            @click="removeExistingImage(image.id)"
                                                            class="hidden group-hover:flex bg-red-500 text-white p-1 rounded text-xs"
                                                            title="Удалить"
                                                        >
                                                            ✕
                                                        </button>
                                                    </div>
                                                    <div v-if="storeForm.main_photo_id === image.id"
                                                         class="absolute top-1 left-1 bg-indigo-500 text-white text-xs px-1 rounded">
                                                        Главное
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Новые изображения -->
                                        <div v-if="newImages.length > 0" class="mb-4">
                                            <label class="block text-xs font-medium text-gray-500 mb-2">Новые фото:</label>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                                                <div v-for="(image, index) in newImages" :key="index" class="relative group">
                                                    <img
                                                        :src="image.preview"
                                                        class="w-full h-24 object-cover rounded-lg border-2"
                                                        :class="{ 'border-indigo-500 ring-2 ring-indigo-300': image.is_main }"
                                                    >
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all rounded-lg flex items-center justify-center gap-1">
                                                        <button
                                                            type="button"
                                                            @click="setNewImageAsMain(index)"
                                                            class="hidden group-hover:flex bg-green-500 text-white p-1 rounded text-xs"
                                                            title="Сделать главным"
                                                        >
                                                            ★
                                                        </button>
                                                        <button
                                                            type="button"
                                                            @click="removeNewImage(index)"
                                                            class="hidden group-hover:flex bg-red-500 text-white p-1 rounded text-xs"
                                                            title="Удалить"
                                                        >
                                                            ✕
                                                        </button>
                                                    </div>
                                                    <div v-if="image.is_main "
                                                         class="absolute top-1 left-1 bg-indigo-500 text-white text-xs px-1 rounded">
                                                        Главное
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Кнопка загрузки -->
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600">
                                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                        <span>Загрузить фото</span>
                                                        <input id="file-upload" type="file" class="sr-only" accept="image/*" multiple @change="onImagesSelect">
                                                    </label>
                                                    <p class="pl-1">или перетащите файлы</p>
                                                </div>
                                                <p class="text-xs text-gray-500">PNG, JPG до 5 МБ каждый</p>
                                            </div>
                                        </div>
                                        <div v-if="imageError" class="mt-2 text-xs text-red-600">{{ imageError }}</div>
                                        <div class="text-xs text-gray-500 mt-2">
                                            💡 Первое загруженное фото автоматически становится главным. Вы можете изменить это позже.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="submit"
                                    :disabled="saving"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gradient-to-r from-pink-500 to-yellow-500 text-base font-medium text-white hover:from-pink-600 hover:to-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ saving ? 'Сохранение...' : (editingStore ? 'Сохранить' : 'Добавить') }}
                                </button>
                                <button
                                    type="button"
                                    @click="showModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Отмена
                                </button>
                                <button
                                    v-if="editingStore"
                                    type="button"
                                    @click="deleteStore(editingStore)"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Удалить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<script>
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'
import L from 'leaflet'
import axios from 'axios'

// Иконки Leaflet
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
})

const redIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
})

const greenIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
})

export default {
    name: 'StoresIndex',
    components: {
        AuthenticatedLayout,
        LMap,
        LTileLayer,
        LMarker,
        LPopup
    },
    props: {
        stores: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            map: null,
            zoom: 12,
            center: [53.893009, 27.567444],
            showMapMarkers: true,
            selectedStore: null,
            showModal: false,
            editingStore: null,
            saving: false,
            imageError: '',
            geocoding: false,
            geocodeError: '',
            currentMarkerLat: null,
            currentMarkerLng: null,
            currentMarkerIcon: greenIcon,
            existingImages: [], // Существующие изображения
            newImages: [], // Новые изображения { file, preview, is_main }
            imagesToDelete: [], // ID изображений для удаления
            storeForm: {
                name: '',
                address: '',
                phone: '',
                opening_hours: '',
                lat: '',
                lng: '',
                main_photo_id: '' // ID главного изображения
            },
            router: router,
            showImageModal: false,
            enlargedImageUrl: null,
            enlargedImageAlt: ''
        }
    },
    mounted() {
        if (this.stores.data?.length && this.stores.data[0]?.lat) {
            this.center = [this.stores.data[0].lat, this.stores.data[0].lng]
        }
    },
    methods: {
        getMainImage(store) {
            if (!store.images || store.images.length === 0) {
                return null
            }

            // Если есть главное фото, показываем его
            if (store.main_photo_id) {
                const mainImage = store.images.find(img => img.id === store.main_photo_id)
                if (mainImage) {
                    return mainImage
                }
            }

            // Иначе показываем первое фото
            return store.images[0]
        },

        getMainImageUrl(store) {
            const mainImage = this.getMainImage(store)
            return mainImage ? mainImage.big_uri : null
        },
        enlargeImage(imageUrl, imageAlt) {
            this.enlargedImageUrl = imageUrl
            this.enlargedImageAlt = imageAlt
            this.showImageModal = true
        },

        closeImageModal() {
            this.showImageModal = false
            this.enlargedImageUrl = null
            this.enlargedImageAlt = null
        },
        onMapReady() {
            if (this.stores.data?.length) {
                const validStores = this.stores.data.filter(s => s.lat && s.lng)
                if (validStores.length > 0) {
                    const bounds = L.latLngBounds(validStores.map(s => [s.lat, s.lng]))
                    if (bounds.isValid()) {
                        this.map.leafletObject.fitBounds(bounds)
                    }
                }
            }
        },

        onMapClick(event) {
            if (this.showModal) {
                this.storeForm.lat = event.latlng.lat.toFixed(7)
                this.storeForm.lng = event.latlng.lng.toFixed(7)
                this.updateMarkerFromCoords()
            }
        },

        selectStore(store) {
            this.selectedStore = store
        },

        openAddModal() {
            this.editingStore = null
            this.resetForm()
            this.showModal = true
            this.currentMarkerIcon = greenIcon
        },

        editStore(store) {
            this.editingStore = store
            this.storeForm.name = store.name
            this.storeForm.address = store.address
            this.storeForm.phone = store.phone
            this.storeForm.opening_hours = store.opening_hours || ''
            this.storeForm.lat = store.lat || ''
            this.storeForm.lng = store.lng || ''
            this.storeForm.main_photo_id = store.main_photo_id  // Исправлено

            // Загружаем существующие изображения
            this.existingImages = store.images || []

            this.currentMarkerIcon = redIcon
            this.showModal = true

            if (store.lat && store.lng && this.map) {
                setTimeout(() => {
                    this.map.leafletObject.setView([store.lat, store.lng], 15)
                }, 100)
            }
        },

        async geocodeAddress() {
            if (!this.storeForm.address || this.storeForm.address.length < 5) return

            this.geocoding = true
            this.geocodeError = ''

            try {
                const response = await axios.post(route('stores.geocode'), {
                    address: this.storeForm.address
                })

                if (response.data.success && response.data.lat && response.data.lon) {
                    this.storeForm.lat = response.data.lat
                    this.storeForm.lng = response.data.lon
                    this.updateMarkerFromCoords()

                    if (this.map) {
                        this.map.leafletObject.setView([response.data.lat, response.data.lon], 15)
                    }
                } else {
                    this.geocodeError = 'Не удалось определить координаты по адресу. Уточните адрес.'
                }
            } catch (error) {
                console.error('Geocoding error:', error)
                this.geocodeError = 'Ошибка при определении координат. Попробуйте позже.'
            } finally {
                this.geocoding = false
            }
        },

        updateMarkerFromCoords() {
            if (this.storeForm.lat && this.storeForm.lng) {
                this.currentMarkerLat = parseFloat(this.storeForm.lat)
                this.currentMarkerLng = parseFloat(this.storeForm.lng)

                if (this.map) {
                    this.map.leafletObject.setView([this.currentMarkerLat, this.currentMarkerLng], 15)
                }
            }
        },

        onImagesSelect(event) {
            const files = Array.from(event.target.files)
            if (files.length === 0) return

            this.imageError = ''

            // Проверка файлов
            const validFiles = []
            for (const file of files) {
                if (!file.type.startsWith('image/')) {
                    this.imageError = 'Пожалуйста, выбирайте только изображения'
                    continue
                }

                if (file.size > 5 * 1024 * 1024) {
                    this.imageError = 'Размер файла не должен превышать 5 МБ'
                    continue
                }

                validFiles.push(file)
            }

            if (validFiles.length === 0) return

            // Добавляем новые изображения
            const isFirstImage = this.newImages.length === 0 && this.existingImages.length === 0
            for (const file of validFiles) {
                this.newImages.push({
                    file: file,
                    preview: URL.createObjectURL(file),
                    is_main: isFirstImage && this.newImages.length === 0 && this.existingImages.length === 0
                })
            }

            // Очищаем input
            event.target.value = ''
        },

        setNewImageAsMain(index) {
            // Сбрасываем главное у всех новых изображений
            this.newImages.forEach(img => img.is_main = false)
            // Устанавливаем главное у выбранного
            this.newImages[index].is_main = true
            // Сбрасываем главное у существующих
            this.storeForm.main_photo_id = null
        },

        setMainImage(imageId) {
            this.storeForm.main_photo_id = imageId
            // Сбрасываем главное у новых изображений
            this.newImages.forEach(img => img.is_main = false)
        },

        removeNewImage(index) {
            URL.revokeObjectURL(this.newImages[index].preview)
            this.newImages.splice(index, 1)
        },

        async removeExistingImage(imageId) {
            if (confirm('Удалить это изображение?')) {
                this.imagesToDelete.push(imageId)
                const index = this.existingImages.findIndex(img => img.id === imageId)
                if (index !== -1) {
                    this.existingImages.splice(index, 1)
                }
                if (this.storeForm.main_photo_id === imageId) {
                    this.storeForm.main_photo_id = null
                }
            }
        },

        async saveStore() {
            this.saving = true
            try {
                const formData = new FormData()
                formData.append('name', this.storeForm.name)
                formData.append('address', this.storeForm.address)
                formData.append('phone', this.storeForm.phone)
                if (this.storeForm.opening_hours) formData.append('opening_hours', this.storeForm.opening_hours)
                if (this.storeForm.lat) formData.append('lat', this.storeForm.lat)
                if (this.storeForm.lng) formData.append('lng', this.storeForm.lng)

                // ВАЖНО: используем main_photo_id, а не main_photo_id
                if (this.storeForm.main_photo_id) {
                    formData.append('main_photo_id', this.storeForm.main_photo_id)
                }

                // Добавляем новые файлы
                if (this.newImages.length > 0) {
                    this.newImages.forEach((image, index) => {
                        formData.append(`images[${index}]`, image.file)
                        if (image.is_main) {
                            formData.append('new_main_image_index', index)
                        }
                    })
                }

                // Добавляем ID изображений для удаления
                if (this.imagesToDelete.length > 0) {
                    this.imagesToDelete.forEach(id => {
                        formData.append('delete_images[]', id)
                    })
                }

                let response
                if (this.editingStore) {
                    formData.append('_method', 'PUT')
                    response = await axios.post(route('stores.update', this.editingStore.id), formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    })
                } else {
                    response = await axios.post(route('stores.store'), formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    })
                }

                if (response.data.success) {
                    this.showModal = false
                    this.resetForm()
                    router.reload()
                }
            } catch (error) {
                console.error('Ошибка сохранения:', error)
                if (error.response?.data?.errors) {
                    const messages = Object.values(error.response.data.errors).flat().join('\n')
                    alert('Ошибка валидации:\n' + messages)
                } else {
                    alert('Ошибка при сохранении: ' + (error.response?.data?.message || error.message))
                }
            } finally {
                this.saving = false
            }
        },
        async deleteStore(store) {
            if (!confirm(`Вы уверены, что хотите удалить точку "${store.name}"?`)) return

            try {
                const response = await axios.delete(route('stores.destroy', store.id))
                if (response.data.success) {
                    if (this.editingStore) this.showModal = false
                    router.reload()
                }
            } catch (error) {
                console.error('Ошибка удаления:', error)
                alert('Ошибка при удалении: ' + (error.response?.data?.message || error.message))
            }
        },

        resetForm() {
            // Очищаем превью новых изображений
            this.newImages.forEach(img => URL.revokeObjectURL(img.preview))

            this.storeForm = {
                name: '',
                address: '',
                phone: '',
                opening_hours: '',
                lat: '',
                lng: '',
                main_photo_id: null
            }
            this.existingImages = []
            this.newImages = []
            this.imagesToDelete = []
            this.imageError = ''
            this.geocodeError = ''
            this.currentMarkerLat = null
            this.currentMarkerLng = null
        }
    },
    beforeDestroy() {
        // Очищаем все превью при уничтожении компонента
        this.newImages.forEach(img => URL.revokeObjectURL(img.preview))
    }
}
</script>

<style>
.leaflet-control-attribution {
    display: none !important;
}
.leaflet-control {
    opacity: 0.7;
}
</style>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .sm\:align-middle,
.modal-leave-active .sm\:align-middle {
    transition: transform 0.3s ease;
}

.modal-enter-from .sm\:align-middle,
.modal-leave-to .sm\:align-middle {
    transform: scale(0.9);
}

:deep(.leaflet-popup-content) {
    margin: 0;
    min-width: 250px;
}

:deep(.leaflet-popup-content-wrapper) {
    padding: 0;
    border-radius: 12px;
    overflow: hidden;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
