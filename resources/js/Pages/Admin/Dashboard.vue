<script setup>
import PlatformAdminLayout from '@/Layouts/PlatformAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Админ — дашборд" />

    <PlatformAdminLayout>
        <template #title>Панель платформы</template>

        <div class="mx-auto max-w-5xl">
            <h2 class="text-2xl font-semibold text-white">
                Обзор
            </h2>
            <p class="mt-1 text-sm text-slate-400">
                Заявки на регистрацию салонов и подключённые организации.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <Link
                    :href="route('admin.register-requests.index', { status: 'pending' })"
                    class="rounded-xl border border-slate-800 bg-slate-900 p-6 transition hover:border-indigo-500/50"
                >
                    <p class="text-sm text-slate-400">
                        Ожидают решения
                    </p>
                    <p class="mt-2 text-3xl font-bold text-white">
                        {{ stats.pending_requests }}
                    </p>
                </Link>
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                    <p class="text-sm text-slate-400">
                        Организации
                    </p>
                    <p class="mt-2 text-3xl font-bold text-white">
                        {{ stats.organizations }}
                    </p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                    <p class="text-sm text-slate-400">
                        Пользователи бизнеса
                    </p>
                    <p class="mt-2 text-3xl font-bold text-white">
                        {{ stats.business_users }}
                    </p>
                </div>
            </div>
        </div>
    </PlatformAdminLayout>
</template>
