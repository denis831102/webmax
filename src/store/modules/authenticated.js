//import axios from "axios";
import { HTTP } from "@/hooks/http";

export default {
  state: {
    isAuthenticated: false,
    curUser: {},
  },
  actions: {
    async checkAuthenticated({ commit }, form) {
      try {
        const curDate = new Date(),
          curTime = [
            curDate.getHours(),
            curDate.getMinutes(),
            curDate.getSeconds(),
          ].join(":");

        const response = await HTTP.get("", {
          params: {
            _method: "checkAuthenticated",
            _login: form.login,
            _password: form.password,
            _date: curDate,
            _time: curTime,
          },
        });

        if (response.data.isSuccesfull) {
          commit("changeAuthenticated", true);
          commit("setCurUser", {
            id: response.data.id,
            PIB: response.data.PIB,
            idStatus: response.data.idStatus,
            nameStatus: response.data.nameStatus,
            listAccess: response.data.listAccess,
            login: form.login,
            password: form.password,
          });
        } else {
          commit("changeAuthenticated", false);
          commit("setCurUser", { id: 0, PIB: "", login: "", password: "" });
        }
      } catch (e) {
        alert("Помилка авторизації користувача");
      }
    },
  },
  mutations: {
    changeAuthenticated(state, val) {
      state.isAuthenticated = val;
    },
    setCurUser(state, user) {
      state.curUser = user;
      HTTP.defaults.headers["Token"] = btoa(`${user.login}:${user.password}`);
    },
  },
  getters: {
    getAuthenticated(state) {
      return state.isAuthenticated;
    },
    getCurUser(state) {
      return state.curUser;
    },
  },
  //namespaced: true,
};
