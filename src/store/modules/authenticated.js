// import axios from "axios";
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
            PIB: response.data.PIB,
            login: form.login,
            password: form.password,
          });
        } else {
          commit("changeAuthenticated", false);
          commit("setCurUser", { PIB: "", login: "", password: "" });
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
    setUsers(state, data) {
      state.users = data;
    },
    setCurUser(state, user) {
      state.curUser = user;
      HTTP.defaults.headers["token"] = btoa(`${user.login}:${user.password}`);
    },
  },
  getters: {
    getAuthenticated(state) {
      return state.isAuthenticated;
    },
    getUsers(state) {
      return state.users;
    },
    getCurUser(state) {
      return state.curUser;
    },
  },
  //namespaced: true,
};
