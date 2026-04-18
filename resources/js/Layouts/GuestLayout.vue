<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center  bg-gradient-to-br  from-purple-900 via-indigo-900 to-blue-900  pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/" class="flex items-center space-x-3 group">
                <div class="relative">
                    <div class="w-12 h-12 bg-gradient-to-r from-pink-500 to-yellow-500 rounded-2xl rotate-3 group-hover:rotate-6 transition-transform duration-300"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-white drop-shadow-lg">🎁</span>
                    </div>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-pink-300 to-yellow-300 bg-clip-text text-transparent">
                            GiftHub
                        </span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-lg sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
