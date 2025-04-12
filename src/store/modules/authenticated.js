import axios from "axios";

export default {
  state: {
    key: 123,
    server: `https://webmax.lond.lg.ua/php/Server.php`,
    isAuthenticated: false,
    // users: [{PIB: "", login: "", password: 123 }],
    users: [],
    curUser: {},
  },
  actions: {
    async fetchUsers({ state, commit /*getters, dispatch,*/ }) {
      try {
        const response = await axios.get(state.server, {
          params: {
            _key: state.key,
            _method: "getUsers",
          },
        });

        // state.users = response.data;
        commit("setUsers", response.data);
      } catch (e) {
        alert("Помилка одержання користувачів");
      }
    },

    async changeDateUser({ state }, data) {
      try {
        await axios.get(state.server, {
          params: {
            _key: state.key,
            _method: "changeDateUser",
            _id: data.id,
            _date: data.curDate,
            _time: data.curTime,
          },
        });
        //
      } catch (e) {
        alert("Помилка збереження змін у користувача");
      }
    },

    checkAuthenticated({ state, commit, dispatch }, form) {
      let curDate = new Date(),
        curTime = `${curDate.getHours()}:${curDate.getMinutes()}:${curDate.getSeconds()}`;

      commit("changeAuthenticated", false);
      commit("setCurUser", {});
      state.users.forEach((user) => {
        if (form.password == user.password) {
          commit("changeAuthenticated", true);
          commit("setCurUser", user);
          dispatch("changeDateUser", { id: user.id, curDate, curTime });
        }
      });
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
