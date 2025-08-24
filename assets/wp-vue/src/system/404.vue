<template>
    <div
        class="min-h-screen bg-gray-50 flex flex-col justify-center items-center px-6 py-12"
    >
        <!-- 404 SVG Illustration -->
        <svg
            class="w-96 h-96 mb-8"
            viewBox="0 0 800 600"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <!-- Background Circle -->
            <circle cx="400" cy="300" r="250" fill="#EEF2FF" />

            <!-- 4 -->
            <path
                d="M270 180V360M270 280H370"
                stroke="#6366F1"
                stroke-width="40"
                stroke-linecap="round"
            />
            <path
                d="M370 180V360"
                stroke="#6366F1"
                stroke-width="40"
                stroke-linecap="round"
            />

            <!-- 0 -->
            <circle
                cx="470"
                cy="270"
                r="90"
                stroke="#6366F1"
                stroke-width="40"
            />

            <!-- 4 -->
            <path
                d="M570 180V360M570 280H670"
                stroke="#6366F1"
                stroke-width="40"
                stroke-linecap="round"
            />
            <path
                d="M670 180V360"
                stroke="#6366F1"
                stroke-width="40"
                stroke-linecap="round"
            />

            <!-- Decorative Elements -->
            <circle cx="200" cy="150" r="20" fill="#818CF8" />
            <circle cx="650" cy="450" r="15" fill="#818CF8" />
            <circle cx="720" cy="200" r="10" fill="#818CF8" />
            <circle cx="150" cy="400" r="25" fill="#818CF8" />

            <!-- Abstract Lines -->
            <path d="M100 200L150 250" stroke="#818CF8" stroke-width="4" />
            <path d="M700 350L750 400" stroke="#818CF8" stroke-width="4" />
            <path d="M150 500L200 550" stroke="#818CF8" stroke-width="4" />
        </svg>

        <!-- Content -->
        <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
            <h2 class="text-2xl font-medium text-gray-700 mb-4">
                Page Not Found
            </h2>
            <p class="text-gray-500 mb-8 max-w-md">
                The page you're looking for doesn't exist or has been moved.
            </p>
            <router-link
                to="/"
                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
            >
                Return Home
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        name: '404',
    }
</script>
