<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    sku: '',
    price: '',
    active: true,
});

const submit = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <Head title="Новый товар" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Новый товар
                </h2>
                <Link
                    :href="route('products.index')"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    Назад к списку
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Название
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="sku" class="block text-sm font-medium text-gray-700">
                                    Артикул (SKU)
                                </label>
                                <input
                                    id="sku"
                                    v-model="form.sku"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div v-if="form.errors.sku" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.sku }}
                                </div>
                            </div>
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">
                                    Цена
                                </label>
                                <input
                                    id="price"
                                    v-model="form.price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                                <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.price }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="active"
                                v-model="form.active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label for="active" class="text-sm text-gray-700">
                                Активен
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Link
                                :href="route('products.index')"
                                class="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Отмена
                            </Link>
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                :disabled="form.processing"
                            >
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

