import { createApp } from "vue";
import { createWebHashHistory, createRouter } from "vue-router";
import { freeRoutes } from "./routes"; // Import your router
import App from "./App.vue"; // Your root component
import "@/assets/index.css";
import { createPinia } from "pinia";
import authorizationMiddleware from "./routes/authorization.middleware";

// Create Vue application
const app = createApp(App);
const pinia = createPinia();

const router = createRouter({
  history: createWebHashHistory(),
  routes: freeRoutes,
});

app.use(router);
app.use(pinia);
authorizationMiddleware(router);
app.mount("#app");
