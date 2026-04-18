<script setup>
import PlatformAdminLayout from '@/Layouts/PlatformAdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, () => {
    router.get(
        route('admin.business-users.index'),
        { search: search.value },
        { preserveState: true, replace: true },
    );
});

const blockUser = (userId) => {
    const reason = prompt('Причина блокировки (необязательно):') ?? '';
    router.post(route('admin.business-users.block', userId), {
        block_reason: reason,
    });
};

const unblockUser = (userId) => {
    router.post(route('admin.business-users.unblock', userId));
};

const deleteUser = (userId) => {
    if (confirm('Удалить учётную запись бизнес-пользователя?')) {
        router.delete(route('admin.business-users.destroy', userId));
    }
};
</script>

<template>
    <Head title="Бизнес-пользователи" />

    <PlatformAdminLayout>
        <template #title>Управление бизнесами</template>

        <div class="mx-auto max-w-6xl">
            <h2 class="text-2xl font-semibold text-white">
                Бизнес-пользователи
            </h2>
            <p class="mt-1 text-sm text-slate-400">
                Блокировка, разблокировка и удаление учётных записей (ТЗ п. 2.1).
            </p>

            <input
                v-model="search"
                type="search"
                placeholder="Поиск по имени, email, организации"
                class="mt-6 w-full max-w-md rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white"
            >

            <div class="mt-6 overflow-hidden rounded-xl border border-slate-800">
                <table class="min-w-full divide-y divide-slate-800 text-left text-sm">
                    <thead class="bg-slate-900/80 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">
                                Пользователь
                            </th>
                            <th class="px-4 py-3">
                                Организация
                            </th>
                            <th class="px-4 py-3">
                                Статус
                            </th>
                            <th class="px-4 py-3 text-right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-slate-900/40">
                        <tr
                            v-for="u in users.data"
                            :key="u.id"
                        >
                            <td class="px-4 py-3">
                                <div class="font-medium text-white">
                                    {{ u.name }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ u.email }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-300">
                                {{ u.organization?.name || '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    v-if="u.is_blocked"
                                    class="rounded-full bg-rose-500/20 px-2 py-0.5 text-xs text-rose-300"
                                >
                                    Заблокирован
                                </span>
                                <span
                                    v-else
                                    class="rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs text-emerald-300"
                                >
                                    Активен
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs">
                                <button
                                    v-if="!u.is_blocked"
                                    type="button"
                                    class="text-rose-400 hover:text-rose-300"
                                    @click="blockUser(u.id)"
                                >
                                    Блокировать
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    class="text-emerald-400 hover:text-emerald-300"
                                    @click="unblockUser(u.id)"
                                >
                                    Разблокировать
                                </button>
                                <span class="mx-2 text-slate-600">|</span>
                                <button
                                    type="button"
                                    class="text-slate-400 hover:text-white"
                                    @click="deleteUser(u.id)"
                                >
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <Link
                    :href="route('admin.register-requests.index')"
                    class="text-sm text-indigo-400 hover:text-indigo-300"
                >
                    ← Заявки на верификацию (ожидают / подтверждены / отклонены)
                </Link>
            </div>
        </div>
    </PlatformAdminLayout>
</template>
