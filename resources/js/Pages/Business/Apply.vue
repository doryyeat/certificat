<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    form_of_own: '',
    contact: '',
    phone: '',
    address: '',
    bank_info: '',
    unp: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('business.register-request'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Заявка на подключение бизнеса" />

        <div class="w-full">
            <h1 class="text-xl font-semibold text-gray-900">
                Заявка на подключение бизнеса
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                После проверки администратор платформы создаст вашу организацию и учётную запись. Вход — с указанным email и паролем.
            </p>

            <form
                class="mt-6 space-y-4"
                @submit.prevent="submit"
            >
                <div>
                    <label class="block text-xs font-medium text-gray-700">Название организации</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Форма собственности</label>
                    <input
                        v-model="form.form_of_own"
                        type="text"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        placeholder="ИП, ООО…"
                        required
                    />
                    <p
                        v-if="form.errors.form_of_own"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.form_of_own }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Контактное лицо</label>
                    <input
                        v-model="form.contact"
                        type="text"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Телефон</label>
                    <input
                        v-model="form.phone"
                        type="text"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Email (логин)</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Пароль</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Подтверждение пароля</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Юридический адрес</label>
                    <textarea
                        v-model="form.address"
                        rows="2"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">УНП</label>
                    <input
                        v-model="form.unp"
                        type="text"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Банковские реквизиты</label>
                    <textarea
                        v-model="form.bank_info"
                        rows="2"
                        class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm"
                        required
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800"
                    :disabled="form.processing"
                >
                    Отправить заявку
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
