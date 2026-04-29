<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    currentPlan: String,
    plans: Object,
    feePercent: Number,
});

const form = useForm({
    plan_name: props.currentPlan,
});

const submit = () => {
    form.put(route('business.tariff.update'));
};
</script>

<template>
    <Head title="Тариф GiftHub" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Тариф и комиссия
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
                    Текущая транзакционная комиссия по вашему тарифу:
                    <strong>{{ feePercent }}%</strong>
                    (Free 3%, Start 2%, Pro 1,5%)
                </div>

                <form
                    class="mt-6 space-y-4 rounded-lg bg-white p-6 shadow"
                    @submit.prevent="submit"
                >
                    <div
                        v-for="(meta, key) in plans"
                        :key="key"
                        class="flex cursor-pointer items-start gap-3 rounded-lg border p-4"
                        :class="form.plan_name === key ? 'border-indigo-500 bg-indigo-50/50' : 'border-gray-200'"
                    >
                        <input
                            :id="'plan-' + key"
                            v-model="form.plan_name"
                            type="radio"
                            name="plan_name"
                            :value="key"
                            class="mt-1"
                        >
                        <label
                            :for="'plan-' + key"
                            class="flex-1 cursor-pointer"
                        >
                            <span class="font-semibold text-gray-900">{{ meta.label }}</span>
                            <span class="ml-2 text-gray-600">{{ meta.price_byn }} BYN/мес</span>
                            <span class="block text-sm text-gray-500">Комиссия: {{ meta.commission }}</span>
                        </label>
                    </div>

<!--                    <p class="text-xs text-gray-500">-->
<!--                        Переход на Start/Pro в демо применяется без реальной оплаты. Для продакшена подключите платёжный агрегатор (webhook + REST).-->
<!--                    </p>-->

                    <button
                        type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800"
                        :disabled="form.processing"
                    >
                        Сохранить тариф
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
