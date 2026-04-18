<script setup>
import PlatformAdminLayout from '@/Layouts/PlatformAdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    requests: Object,
    filters: Object,
});

const status = ref(props.filters.status || '');

watch(status, () => {
    router.get(
        route('admin.register-requests.index'),
        { status: status.value },
        { preserveState: true, replace: true },
    );
});

const statusLabel = (s) =>
    ({
        pending: 'Ожидает',
        accepted: 'Принята',
        rejected: 'Отклонена',
    }[s] || s);
</script>

<template>
    <Head title="Заявки на регистрацию" />

    <PlatformAdminLayout>
        <template #title>Заявки бизнеса</template>

        <div class="mx-auto max-w-6xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-white">
                        Заявки на подключение
                    </h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Проверьте реквизиты и примите или отклоните заявку.
                    </p>
                </div>
                <div>
                    <label class="block text-xs text-slate-500">Статус</label>
                    <select
                        v-model="status"
                        class="mt-1 rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white"
                    >
                        <option value="">
                            Все
                        </option>
                        <option value="pending">
                            Ожидают
                        </option>
                        <option value="accepted">
                            Принятые
                        </option>
                        <option value="rejected">
                            Отклонённые
                        </option>
                    </select>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-slate-800">
                <table class="min-w-full divide-y divide-slate-800 text-left text-sm">
                    <thead class="bg-slate-900/80 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">
                                Организация
                            </th>
                            <th class="px-4 py-3">
                                Контакт / email
                            </th>
                            <th class="px-4 py-3">
                                Статус
                            </th>
                            <th class="px-4 py-3">
                                Дата
                            </th>
                            <th class="px-4 py-3 text-right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-slate-900/40">
                        <tr
                            v-for="row in requests.data"
                            :key="row.id"
                            class="hover:bg-slate-800/50"
                        >
                            <td class="px-4 py-3 font-medium text-white">
                                {{ row.name }}
                            </td>
                            <td class="px-4 py-3 text-slate-300">
                                <div>{{ row.contact }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ row.email }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="{
                                        'bg-amber-500/20 text-amber-300':
                                            row.status === 'pending',
                                        'bg-emerald-500/20 text-emerald-300':
                                            row.status === 'accepted',
                                        'bg-rose-500/20 text-rose-300':
                                            row.status === 'rejected',
                                    }"
                                >
                                    {{ statusLabel(row.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ new Date(row.created_at).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link
                                    :href="route('admin.register-requests.show', row.id)"
                                    class="text-indigo-400 hover:text-indigo-300"
                                >
                                    Открыть
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!requests.data?.length">
                            <td
                                colspan="5"
                                class="px-4 py-8 text-center text-slate-500"
                            >
                                Заявок нет.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="requests.links?.length > 3"
                class="mt-4 flex flex-wrap gap-2"
            >
                <component
                    :is="link.url ? 'a' : 'span'"
                    v-for="link in requests.links"
                    :key="link.label"
                    :href="link.url"
                    class="rounded border border-slate-700 px-3 py-1 text-xs"
                    :class="link.active ? 'border-indigo-500 bg-indigo-950 text-indigo-200' : 'text-slate-400'"
                    @click.prevent="link.url && router.visit(link.url)"
                    v-html="link.label"
                />
            </div>
        </div>
    </PlatformAdminLayout>
</template>
