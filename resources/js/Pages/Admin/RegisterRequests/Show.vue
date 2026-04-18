<script setup>
import PlatformAdminLayout from '@/Layouts/PlatformAdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    request: Object,
});

const rejectForm = useForm({
    reason: '',
});

const reject = () => {
    rejectForm.post(
        route('admin.register-requests.reject', props.request.id),
        { preserveScroll: true },
    );
};

const accept = () => {
    if (
        !confirm(
            'Создать организацию и пользователя с email из заявки? Пароль — тот, что указал заявитель.',
        )
    ) {
        return;
    }
    router.post(route('admin.register-requests.accept', props.request.id));
};
</script>

<template>
    <Head :title="`Заявка: ${request.name}`" />

    <PlatformAdminLayout>
        <template #title>Заявка #{{ request.id }}</template>

        <div class="mx-auto max-w-3xl">
            <Link
                :href="route('admin.register-requests.index')"
                class="text-sm text-indigo-400 hover:text-indigo-300"
            >
                ← К списку заявок
            </Link>

            <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900/50 p-6">
                <h2 class="text-xl font-semibold text-white">
                    {{ request.name }}
                </h2>
                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">
                        <dt class="text-slate-500">
                            Форма собственности
                        </dt>
                        <dd class="text-slate-200">
                            {{ request.form_of_own }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">
                        <dt class="text-slate-500">
                            Контактное лицо
                        </dt>
                        <dd class="text-slate-200">
                            {{ request.contact }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">
                        <dt class="text-slate-500">
                            Телефон
                        </dt>
                        <dd class="text-slate-200">
                            {{ request.phone }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">
                        <dt class="text-slate-500">
                            Email (логин)
                        </dt>
                        <dd class="text-slate-200">
                            {{ request.email }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">
                        <dt class="text-slate-500">
                            Адрес
                        </dt>
                        <dd class="text-right text-slate-200">
                            {{ request.address }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-800 pb-3">
                        <dt class="text-slate-500">
                            УНП
                        </dt>
                        <dd class="text-slate-200">
                            {{ request.unp }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 pb-3">
                        <dt class="text-slate-500">
                            Банковские реквизиты
                        </dt>
                        <dd class="max-w-md text-right text-slate-200">
                            {{ request.bank_info }}
                        </dd>
                    </div>
                </dl>

                <div
                    v-if="request.status === 'accepted' && request.organization"
                    class="mt-6 rounded-lg border border-emerald-800/50 bg-emerald-950/30 p-4 text-sm text-emerald-200"
                >
                    <p>Принята: организация ID {{ request.organization.id }}, пользователь создан.</p>
                </div>

                <div
                    v-if="request.status === 'rejected' && request.reason"
                    class="mt-6 rounded-lg border border-rose-800/50 bg-rose-950/30 p-4 text-sm text-rose-200"
                >
                    <p class="font-medium">
                        Причина отказа
                    </p>
                    <p class="mt-1 whitespace-pre-wrap">
                        {{ request.reason }}
                    </p>
                </div>

                <div
                    v-if="request.status === 'pending'"
                    class="mt-8 flex flex-col gap-4 border-t border-slate-800 pt-6 sm:flex-row"
                >
                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500"
                        @click="accept"
                    >
                        Принять и создать аккаунт
                    </button>

                    <form
                        class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-end"
                        @submit.prevent="reject"
                    >
                        <div class="flex-1">
                            <label class="block text-xs text-slate-500">Причина отклонения (необязательно)</label>
                            <textarea
                                v-model="rejectForm.reason"
                                rows="2"
                                class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white"
                            />
                        </div>
                        <button
                            type="submit"
                            class="rounded-lg border border-rose-500/50 px-4 py-2 text-sm font-medium text-rose-300 hover:bg-rose-950"
                            :disabled="rejectForm.processing"
                        >
                            Отклонить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </PlatformAdminLayout>
</template>
