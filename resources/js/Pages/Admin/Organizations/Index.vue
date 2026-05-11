<script setup>
import PlatformAdminLayout from '@/Layouts/PlatformAdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    organizations: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, () => {
    router.get(
        route('admin.organizations.index'),
        { search: search.value },
        { preserveState: true, replace: true },
    );
});
</script>

<template>
    <Head title="Организации" />

    <PlatformAdminLayout>
        <template #title>Организации (салоны)</template>

        <div class="mx-auto max-w-6xl">
            <h2 class="text-2xl font-semibold text-white">
                Подключённые организации
            </h2>
            <p class="mt-1 text-sm text-slate-400">
                Тариф, срок подписки и брендинг.
            </p>

            <div class="mt-6">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Поиск по названию или слагу"
                    class="w-full max-w-md rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white placeholder:text-slate-500"
                />
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-slate-800">
                <table class="min-w-full divide-y divide-slate-800 text-left text-sm">
                    <thead class="bg-slate-900/80 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">
                                Название
                            </th>
                            <th class="px-4 py-3">
                                Слаг
                            </th>
                            <th class="px-4 py-3">
                                Тариф
                            </th>
                            <th class="px-4 py-3">
                                Подписка до
                            </th>
                            <th class="px-4 py-3">
                                Пользователи
                            </th>
                            <th class="px-4 py-3 text-right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 bg-slate-900/40">
                        <tr
                            v-for="org in organizations.data"
                            :key="org.id"
                            class="hover:bg-slate-800/50"
                        >
                            <td class="px-4 py-3 font-medium text-white">
                                {{ org.name }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-400">
                                {{ org.slug }}
                            </td>
                            <td class="px-4 py-3 text-slate-300">
                                {{ org.plan_name }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                {{
                                    org.subscription_active_until
                                        ? new Date(
                                            org.subscription_active_until,
                                        ).toLocaleDateString()
                                        : '—'
                                }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                {{ org.users_count }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link
                                    :href="route('admin.organizations.edit', org.id)"
                                    class="text-indigo-400 hover:text-indigo-300"
                                >
                                    Управление
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!organizations.data?.length">
                            <td
                                colspan="6"
                                class="px-4 py-8 text-center text-slate-500"
                            >
                                Организаций пока нет.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="organizations.links?.length > 3"
                class="mt-4 flex flex-wrap gap-2"
            >
                <component
                    :is="link.url ? 'a' : 'span'"
                    v-for="link in organizations.links"
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
