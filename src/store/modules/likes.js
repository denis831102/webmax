export default {
  state: {
    likes: 5,
  },
  actions: {},
  mutations: {
    incrementLikes(state, inc) {
      state.likes += inc;
    },
    decrementLikes(state, dec) {
      state.likes -= dec;
    },
  },
  getters: {
    getLikes(state) {
      return state.likes;
    },
  },
  namespaced: true,
};
