<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    clientType: {
        type: String,
        default: 'client'
    }
});

const form = useForm({
    name: '',
    form_of_own:'',
    contact:'',
    phone:'',
    address:'',
    bank_info:'',
    unp:'',
    email: '',
    password: '',
    password_confirmation: '',
    client_type: props.clientType,
});

const submit = () => {
    if (props.clientType === 'client') {
        form.post(route('register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }else {
        form.post(route('register.request'), {
            onFinish: (res) => alert(res.message)
        });
    }
};
</script>

<template>
    <Head title="Регистрация" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <Link href="/">
                <div class="w-20 h-20 bg-gradient-to-r from-purple-600 to-pink-500 rounded-2xl rotate-3 flex items-center justify-center text-4xl">
                    🎁
                </div>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ props.clientType === 'business' ? 'Регистрация бизнеса' : 'Регистрация покупателя' }}
                </h2>
                <p class="text-sm text-gray-600 mt-2">
                    {{ props.clientType === 'business'
                    ? 'Создайте аккаунт для управления подарочными сертификатами'
                    : 'Создайте аккаунт для покупки подарочных сертификатов'
                    }}
                </p>
            </div>

            <form @submit.prevent="submit">
                <div v-if="props.clientType === 'client'" >
                <div>
                    <InputLabel for="name" value="Имя" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-4">
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Пароль" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password_confirmation" value="Подтвердите пароль" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
                </div>
                <div v-else>
                    <div>
                        <InputLabel for="name" value="Наименование организации" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="form_of_own" value="Форма собственности" />
                        <TextInput
                            id="form_of_own"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.form_of_own"
                            placeholder="ООО,ОАО..."
                            required
                            autofocus
                            autocomplete="form_of_own"
                        />
                        <InputError class="mt-2" :message="form.errors.form_of_own" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="contact" value="Контактное лицо" />
                        <TextInput
                            id="contact"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.contact"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.contact" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="phone" value="Телефон" />
                        <TextInput
                            id="phone"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.phone"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="address" value="Юридический адрес" />
                        <TextInput
                            id="address"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.address"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.address" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="bank_info" value="Банковские реквизиты" />
                        <TextInput
                            id="bank_info"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.bank_info"
                            placeholder="Наименование банка, БИК, расчетный счет"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.bank_info" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="unp" value="УНП" />
                        <TextInput
                            id="unp"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.unp"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.unp" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="password" value="Пароль" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                </div>
                <input type="hidden" v-model="form.client_type" />


                <div class="flex items-center justify-end mt-4">
                    <Link
                        :href="route('login')"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Уже зарегистрированы?
                    </Link>

                    <PrimaryButton v-if="props.clientType !== 'business'" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Зарегистрироваться
                    </PrimaryButton>
                    <PrimaryButton v-else class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Подать заявку
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
