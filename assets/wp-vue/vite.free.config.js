import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import path from "path";
import tailwind from "tailwindcss";
import autoprefixer from "autoprefixer";

// import unmountPlugin from './vite/plugins/unmount.plugin'

export default defineConfig({
  // Look for .env files in the root directory (wp-vue) instead of the Vite root
  // envDir: path.resolve(__dirname, './'),
  plugins: [
    vue({
      template: {
        compilerOptions: {
          // This helps with hydration issues
          whitespace: "preserve",
        },
      },
    }),
  ],
  css: {
    postcss: { plugins: [tailwind(), autoprefixer()] },
  },
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"), // Alias for src directory
    },
  },
  server: {
    open: true,
    port: 3001,
  },

  // Root of the app
  root: "src/packages/free",

  // Automatically resolve public folder path
  publicDir: path.resolve(__dirname, "public"),

  // Correct asset base path for production
  base:
    process.env.NODE_ENV === "production"
      ? "/crafely-smartsales-lite/assets/dist/"
      : "/",

  build: {
    // Output dist inside ../assets/dist (relative to wp-vue)
    outDir: path.resolve(__dirname, "../dist"),
    emptyOutDir: true,
    assetsDir: "",
    chunkSizeWarningLimit: 2000,
    rollupOptions: {
      input: path.resolve(__dirname, "src/packages/free/main.ts"),
      output: {
        format: "es",
        entryFileNames: "js/[name].[hash].js",
        chunkFileNames: "js/[name].[hash].js",
        assetFileNames: "css/[name].[hash].[ext]",
        manualChunks(id) {
          if (id.includes("node_modules")) return "vendor";
          if (id.includes("/modules/")) return "modules";
        },
      },
    },
    target: "es2015",
    minify: "terser",
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
      },
    },
  },
});
