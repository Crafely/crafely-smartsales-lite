<template>
    <div
        class="min-h-screen bg-gray-50 flex flex-col justify-center items-center px-6 py-12"
    >
        <!-- Permission Denied SVG Illustration -->
        <svg
            class="w-96 h-96 mb-8"
            viewBox="0 0 800 600"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <!-- Background Circle -->
            <circle cx="400" cy="300" r="250" fill="#FEE2E2" />

            <!-- Lock Body -->
            <rect
                x="300"
                y="250"
                width="200"
                height="160"
                rx="20"
                fill="#EF4444"
            />

            <!-- Lock Shackle -->
            <path
                d="M360 250V200C360 144.772 404.772 100 460 100V100C515.228 100 560 144.772 560 200V250"
                stroke="#EF4444"
                stroke-width="40"
                stroke-linecap="round"
            />

            <!-- Decorative Elements -->
            <circle cx="200" cy="150" r="20" fill="#FCA5A5" />
            <circle cx="650" cy="450" r="15" fill="#FCA5A5" />
            <circle cx="720" cy="200" r="10" fill="#FCA5A5" />
            <circle cx="150" cy="400" r="25" fill="#FCA5A5" />

            <!-- Abstract Lines -->
            <path d="M100 200L150 250" stroke="#FCA5A5" stroke-width="4" />
            <path d="M700 350L750 400" stroke="#FCA5A5" stroke-width="4" />
            <path d="M150 500L200 550" stroke="#FCA5A5" stroke-width="4" />
        </svg>

        <!-- Content -->
        <div class="text-center">
            <h1 class="text-6xl font-bold text-gray-900 mb-4">Access Denied</h1>
            <h2 class="text-2xl font-medium text-gray-700 mb-4">
                Permission Required
            </h2>
            <p class="text-gray-500 mb-8 max-w-md">
                Sorry, you don't have permission to access this page. Please
                contact your administrator for access.
            </p>
            <router-link
                to="/"
                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
            >
                Back to Home
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'Permission',
    }
</script>
