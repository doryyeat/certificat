<script setup>
import PlatformAdminLayout from '@/Layouts/PlatformAdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    organization: Object,
});

const form = useForm({
    name: props.organization.name,
    slug: props.organization.slug,
    plan_name: props.organization.plan_name,
    subscription_active_until: props.organization.subscription_active_until
        ? props.organization.subscription_active_until.substring(0, 10)
        : '',
    primary_color: props.organization.primary_color || '',
    logo_url: props.organization.logo_url || '',
});

const submit = () => {
    form.put(route('admin.organizations.update', props.organization.id));
};
</script>

<template>
    <Head :title="`Организация: ${organization.name}`" />

    <PlatformAdminLayout>
        <template #title>Редактирование организации</template>

        <div class="mx-auto max-w-2xl">
            <Link
                :href="route('admin.organizations.index')"
                class="text-sm text-indigo-400 hover:text-indigo-300"
            >
                ← К списку
            </Link>

            <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900/50 p-6">
                <p class="text-sm text-slate-500">
                    Пользователей в организации: {{ organization.users_count }}
                </p>

                <form
                    class="mt-6 space-y-4"
                    @submit.prevent="submit"
                >
                    <div>
                        <label class="block text-xs text-slate-500">Название</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-xs text-rose-400"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500">Slug (URL)</label>
                        <input
                            v-model="form.slug"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 font-mono text-sm text-white"
                        />
                        <p
                            v-if="form.errors.slug"
                            class="mt-1 text-xs text-rose-400"
                        >
                            {{ form.errors.slug }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500">Тариф</label>
                        <input
                            v-model="form.plan_name"
                            type="text"
                            class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white"
                        />
                        <p
                            v-if="form.errors.plan_name"
                            class="mt-1 text-xs text-rose-400"
                        >
                            {{ form.errors.plan_name }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500">Подписка активна до</label>
                        <input
                            v-model="form.subscription_active_until"
                            type="date"
                            class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500">Основной цвет (hex)</label>
                        <input
                            v-model="form.primary_color"
                            type="text"
                            placeholder="#6366f1"
                            class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500">URL логотипа</label>
                        <input
                            v-model="form.logo_url"
                            type="url"
                            class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                            :disabled="form.processing"
                        >
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PlatformAdminLayout>
</template>
