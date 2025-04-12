import { createRouter, createWebHistory } from "vue-router";
import { routes } from "./routes";

import { useStore } from "vuex";
import { computed } from "vue";

//const isAuthenticated = true;

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const store = useStore();
  const getAuthenticated = computed(() => store.getters.getAuthenticated);
  //const fetchUsers = () => store.dispatch("fetchUsers");
  //const changeAuthenticated = (val) => store.commit("changeAuthenticated", val);
  //const action = (val) => store.dispatch("action", val);

  if (to.meta.requiresAuth && !getAuthenticated.value) {
    return {
      name: "authent",
    };
  }
});

export default router;
