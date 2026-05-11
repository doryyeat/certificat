<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const avatarPreview = ref(null);
const fileInput = ref(null);

const submitProfile = () => {
    form.put(route('client.profile.update'));
};

const submitPassword = () => {
    form.post(route('client.profile.change-password'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('current_password', 'new_password', 'new_password_confirmation');
        },
    });
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
        // Здесь можно добавить автоматическую загрузку
    }
};
</script>

<template>
    <Head title="Редактирование профиля" />

    <ClientLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Основная информация -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-pink-500">
                        <h2 class="text-xl font-semibold text-white">Редактирование профиля</h2>
                    </div>

                    <form @submit.prevent="submitProfile" class="p-6 space-y-6">
                        <!-- Аватар -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Фото профиля</label>
                            <div class="flex items-center space-x-6">
                                <div class="w-20 h-20 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center text-3xl text-white overflow-hidden">
                                    <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
                                    <span v-else>{{ user.name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleFileChange"
                                    />
                                    <button type="button"
                                            @click="fileInput.click()"
                                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                                        Выбрать фото
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Имя *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
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
                                />
                                <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="+7 (999) 123-45-67"
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <Link :href="route('client.profile.show')"
                                  class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Отмена
                            </Link>
                            <button type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg hover:from-purple-700 hover:to-pink-600 transition disabled:opacity-50">
                                {{ form.processing ? 'Сохранение...' : 'Сохранить изменения' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Смена пароля -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Смена пароля</h2>
                    </div>

                    <form @submit.prevent="submitPassword" class="p-6 space-y-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Текущий пароль *</label>
                                <input
                                    v-model="form.current_password"
                                    type="password"
                                    required
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                />
                                <div v-if="form.errors.current_password" class="mt-1 text-sm text-red-600">{{ form.errors.current_password }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Новый пароль *</label>
                                <input
                                    v-model="form.new_password"
                                    type="password"
                                    required
                                    minlength="8"
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Подтверждение пароля *</label>
                                <input
                                    v-model="form.new_password_confirmation"
                                    type="password"
                                    required
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                />
                                <div v-if="form.errors.new_password" class="mt-1 text-sm text-red-600">{{ form.errors.new_password }}</div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg hover:from-purple-700 hover:to-pink-600 transition disabled:opacity-50">
                                {{ form.processing ? 'Смена...' : 'Сменить пароль' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
