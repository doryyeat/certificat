<script setup>
import InputError from '@/Components/InputError.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    address: '',
    opening_hours: '',
    phone: '',
    lat: '',
    lng: '',
});

const submit = () => {
    form.post(route('stores.store'));
};
</script>

<template>
    <Head title="Новая точка" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">
                    Добавить точку
                </h2>
                <Link
                    :href="route('stores.index')"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    Назад
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form
                    class="space-y-4 rounded-lg bg-white p-6 shadow"
                    @submit.prevent="submit"
                >
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Название</label>
                        <input
                            v-model="form.name"
                            type="text"
                            maxlength="100"
                            class="mt-1 block w-full rounded-md border-gray-300"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.name"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Адрес</label>
                        <textarea
                            v-model="form.address"
                            rows="2"
                            maxlength="500"
                            class="mt-1 block w-full rounded-md border-gray-300"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Часы работы (необязательно)</label>
                        <input
                            v-model="form.opening_hours"
                            type="text"
                            maxlength="255"
                            class="mt-1 block w-full rounded-md border-gray-300"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Телефон (+375XXXXXXXXX)</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="+375291234567"
                            class="mt-1 block w-full rounded-md border-gray-300"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.phone"
                        />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Широта (для карты)</label>
                            <input
                                v-model="form.lat"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Долгота</label>
                            <input
                                v-model="form.lng"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
                            :disabled="form.processing"
                        >
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
