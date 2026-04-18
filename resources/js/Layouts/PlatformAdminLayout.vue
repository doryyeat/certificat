<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const open = ref(false);

function navClass(active) {
    return [
        'block rounded-lg px-3 py-2 text-sm font-medium transition',
        active
            ? 'bg-indigo-600 text-white'
            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
    ];
}
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="lg:flex">
            <aside
                class="hidden w-64 shrink-0 border-r border-slate-800 bg-slate-900/80 lg:flex lg:flex-col"
            >
                <div class="flex h-16 items-center border-b border-slate-800 px-6">
                    <Link
                        :href="route('admin.dashboard')"
                        class="text-lg font-semibold tracking-tight text-white"
                    >
                        GiftHub Admin
                    </Link>
                </div>
                <nav class="flex flex-1 flex-col gap-1 p-4">
                    <Link
                        :href="route('admin.dashboard')"
                        :class="navClass(route().current('admin.dashboard'))"
                    >
                        Дашборд
                    </Link>
                    <Link
                        :href="route('admin.register-requests.index')"
                        :class="navClass(route().current('admin.register-requests.*'))"
                    >
                        Управление бизнесами (заявки)
                    </Link>
                    <Link
                        :href="route('admin.business-users.index')"
                        :class="navClass(route().current('admin.business-users.*'))"
                    >
                        Бизнес-пользователи
                    </Link>
                    <Link
                        :href="route('admin.organizations.index')"
                        :class="navClass(route().current('admin.organizations.*'))"
                    >
                        Организации
                    </Link>
                </nav>
                <div class="border-t border-slate-800 p-4">
                    <Link
                        :href="route('home')"
                        class="text-xs text-slate-500 hover:text-slate-300"
                    >
                        На сайт
                    </Link>
                </div>
            </aside>

            <div class="min-h-screen flex-1">
                <header
                    class="flex h-16 items-center justify-between border-b border-slate-800 bg-slate-900/50 px-4 lg:px-8"
                >
                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-400 hover:bg-slate-800 lg:hidden"
                        @click="open = !open"
                    >
                        <span class="sr-only">Меню</span>
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                    <h1
                        v-if="$slots.title"
                        class="hidden text-sm font-medium text-slate-400 lg:block"
                    >
                        <slot name="title" />
                    </h1>
                    <div class="ms-auto flex items-center gap-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm text-slate-300 hover:bg-slate-800"
                                >
                                    <span>{{ $page.props.auth.user.name }}</span>
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Выйти
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <div
                    v-show="open"
                    class="border-b border-slate-800 bg-slate-900 px-4 py-3 lg:hidden"
                >
                    <ResponsiveNavLink
                        :href="route('admin.dashboard')"
                        :active="route().current('admin.dashboard')"
                    >
                        Дашборд
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('admin.register-requests.index')"
                        :active="route().current('admin.register-requests.*')"
                    >
                        Заявки
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('admin.business-users.index')"
                        :active="route().current('admin.business-users.*')"
                    >
                        Бизнесы
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('admin.organizations.index')"
                        :active="route().current('admin.organizations.*')"
                    >
                        Организации
                    </ResponsiveNavLink>
                </div>

                <main class="px-4 py-8 lg:px-8">
                    <div
                        v-if="$page.props.flash?.success"
                        class="mb-6 rounded-lg border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-200"
                    >
                        {{ $page.props.flash.success }}
                    </div>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
