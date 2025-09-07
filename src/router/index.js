import { createRouter, createWebHistory } from "vue-router";
import { routes } from "./routes";

import { useStore } from "vuex";
import { computed } from "vue";
import { HTTP } from "@/hooks/http";

//const isAuthenticated = true;

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to) => {
  // router.beforeEach((to, from, next) => {
  const store = useStore();
  const getAuthenticated = computed(() => store.getters.getAuthenticated);
  // const getAnswerServer = computed(() => store.getters.getAnswerServer);
  //const fetchUsers = () => store.dispatch("fetchUsers");
  //const changeAuthenticated = (val) => store.commit("changeAuthenticated", val);
  //const action = (val) => store.dispatch("action", val);

  if (to.meta.requiresAuth && !getAuthenticated.value) {
    const response = await HTTP.post("", {
      _method: "checkAuthenticated",
    });

    return {
      // name: "authent",
      name: !+response.data.answer.status ? "block" : "authent",
    };
  }

  // Если маршрут требует логина, а юзер не залогинен
  // if (to.meta.requiresAuth && !getAuthenticated.value) {
  //   next(+getAnswerServer.value.status ? "/authent" : "/block");
  // } else {
  //   // Иначе разрешаем переход
  //   next();
  // }
});

export default router;
