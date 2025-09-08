//import axios from "axios";
import { HTTP } from "@/hooks/http";

export default {
  state: {
    isAuthenticated: false,
    curUser: {},
    settingUser: {
      colOper: "price",
      countTrans: 5,
      isShowMes: 1,
      nameTab: "",
      themaColor: "light",
    },
    answerServer: {},
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

        const response = await HTTP.post("", {
          _method: "checkAuthenticated",
          _login: form.login,
          _password: form.password,
          _date: curDate,
          _time: curTime,
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
          //commit("setAllSettingUser");
        } else {
          commit("changeAuthenticated", false);
          commit("setCurUser", { id: 0, PIB: "", login: "", password: "" });
          commit("changeAnswerServer", response.data.answer);
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
    setAllSettingUser(state) {
      const store = window.localStorage;
      // if (store.getItem("settingUser")) {
      //   state.settingUser = JSON.parse(store.getItem("settingUser"));
      // } else {
      //   store.setItem("settingUser", JSON.stringify(state.settingUser));
      // }
      for (let key in state.settingUser) {
        if (store.getItem(key) !== null) {
          state.settingUser[key] = store.getItem(key);
        } else {
          store.setItem(key, state.settingUser[key]);
        }
      }
    },
    changeSettingUser(state, obj) {
      // for (let key in obj) {
      //   state.settingUser[key] = obj[key];
      // }
      // window.localStorage.setItem(
      //   "settingUser",
      //   JSON.stringify(state.settingUser)
      // );
      for (let key in obj) {
        state.settingUser[key] = obj[key];
        window.localStorage.setItem(key, obj[key]);
      }
    },
    changeAnswerServer(state, val) {
      state.answerServer = val;
    },
  },
  getters: {
    getAuthenticated(state) {
      return state.isAuthenticated;
    },
    getCurUser(state) {
      return state.curUser;
    },
    getSettingUser(state) {
      return state.settingUser;
    },
    getAnswerServer(state) {
      return state.answerServer;
    },
  },
  //namespaced: true,
};
