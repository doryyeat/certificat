<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    customers: Array,
    products: Array,
});

const form = useForm({
    customer_id: '',
    status: 'pending',
    notes: '',
    items: [
        {
            product_id: '',
            name: '',
            price: '',
            quantity: 1,
        },
    ],
    certificate: {
        code: '',
        amount: '',
    },
});

const productOptionsById = computed(() => {
    const map = {};
    for (const p of props.products) {
        map[p.id] = p;
    }
    return map;
});

const addItem = () => {
    form.items.push({
        product_id: '',
        name: '',
        price: '',
        quantity: 1,
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const onProductChange = (index) => {
    const id = form.items[index].product_id;
    const product = productOptionsById.value[id];
    if (product) {
        form.items[index].name = product.name;
        form.items[index].price = product.price;
    }
};

const submit = () => {
    form.post(route('business.orders.store'));
};
</script>

<template>
    <Head title="Новый заказ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Новый заказ
                </h2>
                <Link
                    :href="route('business.orders.index')"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    Назад к заказам
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                Позиции заказа
                            </h3>

                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label
                                            for="customer_id"
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Клиент
                                        </label>
                                        <select
                                            id="customer_id"
                                            v-model="form.customer_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="">
                                                Не выбран
                                            </option>
                                            <option
                                                v-for="customer in customers"
                                                :key="customer.id"
                                                :value="customer.id"
                                            >
                                                {{ customer.name }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="form.errors.customer_id"
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{ form.errors.customer_id }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="status"
                                            class="block text-sm font-medium text-gray-700"
                                        >
                                            Статус
                                        </label>
                                        <select
                                            id="status"
                                            v-model="form.status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="draft">
                                                Черновик
                                            </option>
                                            <option value="pending">
                                                Ожидает оплаты
                                            </option>
                                            <option value="paid">
                                                Оплачен
                                            </option>
                                            <option value="cancelled">
                                                Отменён
                                            </option>
                                        </select>
                                        <div
                                            v-if="form.errors.status"
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{ form.errors.status }}
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-gray-700">
                                            Товары
                                        </h4>
                                        <button
                                            type="button"
                                            class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                                            @click="addItem"
                                        >
                                            Добавить позицию
                                        </button>
                                    </div>

                                    <div class="space-y-3">
                                        <div
                                            v-for="(item, index) in form.items"
                                            :key="index"
                                            class="grid items-end gap-3 rounded-md border border-gray-100 bg-gray-50 p-3 sm:grid-cols-6"
                                        >
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-medium text-gray-600">
                                                    Товар
                                                </label>
                                                <select
                                                    v-model="item.product_id"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    @change="onProductChange(index)"
                                                >
                                                    <option value="">
                                                        Не выбран
                                                    </option>
                                                    <option
                                                        v-for="product in products"
                                                        :key="product.id"
                                                        :value="product.id"
                                                    >
                                                        {{ product.name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-medium text-gray-600">
                                                    Наименование
                                                </label>
                                                <input
                                                    v-model="item.name"
                                                    type="text"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600">
                                                    Цена
                                                </label>
                                                <input
                                                    v-model="item.price"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600">
                                                    Кол-во
                                                </label>
                                                <input
                                                    v-model="item.quantity"
                                                    type="number"
                                                    min="1"
                                                    step="1"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                />
                                            </div>
                                            <div class="sm:col-span-6 flex justify-end">
                                                <button
                                                    v-if="form.items.length > 1"
                                                    type="button"
                                                    class="text-xs text-red-600 hover:text-red-800"
                                                    @click="removeItem(index)"
                                                >
                                                    Удалить
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.items" class="text-sm text-red-600">
                                        {{ form.errors.items }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="notes"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Заметки
                                    </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    <div
                                        v-if="form.errors.notes"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.notes }}
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3">
                                    <Link
                                        :href="route('business.orders.index')"
                                        class="text-sm text-gray-500 hover:text-gray-700"
                                    >
                                        Отмена
                                    </Link>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        :disabled="form.processing"
                                    >
                                        Создать заказ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">
                                Применение сертификата
                            </h3>
                            <p class="mb-2 text-sm text-gray-500">
                                Укажите код сертификата и сумму, которую хотите списать в рамках этого заказа.
                            </p>
                            <div class="space-y-4 text-sm">
                                <div>
                                    <label
                                        for="certificate_code"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Код сертификата
                                    </label>
                                    <input
                                        id="certificate_code"
                                        v-model="form.certificate.code"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 font-mono shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        for="certificate_amount"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Сумма списания
                                    </label>
                                    <input
                                        id="certificate_amount"
                                        v-model="form.certificate.amount"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                                <div
                                    v-if="form.errors['certificate.code'] || form.errors['certificate.amount']"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors['certificate.code'] || form.errors['certificate.amount'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

