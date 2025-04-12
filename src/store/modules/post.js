import axios from "axios";

export const postModule = {
  state: () => ({
    nameAuth: "Denis Ratov",
    posts: [],
    isPostLoading: false,
    selectedSort: "id",
    searchQuery: "",
    page: 1,
    limit: 10,
    totalPages: 0,
    sortOptions: [
      { value: "id", name: "За ID" },
      { value: "title", name: "За назвою" },
      { value: "body", name: "За змістом" },
    ],
  }),
  actions: {
    async fetchPosts({ state, commit /*getters, dispatch,*/ }, count) {
      try {
        // this.isPostLoading = true;
        commit("setLoading", true);
        const response = await axios.get(
          `https://jsonplaceholder.typicode.com/posts`,
          {
            params: {
              _page: state.page,
              _limit: count ? count : state.limit,
            },
          }
        );

        // state.totalPages = ;
        commit(
          "setTotalPages",
          Math.ceil(response.headers["x-total-count"] / state.limit)
        );

        // state.posts = response.data;
        commit("setPosts", response.data);
      } catch (e) {
        alert("Помилка");
      }
    },
    async loadMorePosts({ state, commit /*getters, dispatch,*/ }) {
      try {
        // this.page += 1;
        commit("setPage", state.page + 1);

        // this.isPostLoading = true;
        commit("setLoading", true);

        const response = await axios.get(
          `https://jsonplaceholder.typicode.com/posts`,
          {
            params: {
              _page: state.page,
              _limit: state.limit,
            },
          }
        );

        // this.totalPages = Math.ceil(
        //   response.headers["x-total-count"] / this.limit
        // );
        commit(
          "setTotalPages",
          Math.ceil(response.headers["x-total-count"] / state.limit)
        );

        // this.posts = [...this.posts, ...response.data];
        commit("setPosts", [...state.posts, ...response.data]);
      } catch (e) {
        alert("Помилка авто завантаження");
      }
    },
    removePost({ state, commit }, post) {
      commit(
        "setPosts",
        state.posts.filter((p) => p.id !== post.id)
      );
    },
  },
  mutations: {
    setPosts(state, posts) {
      state.posts = posts;
    },
    setLoading(state, bool) {
      state.isPostLoading = bool;
    },
    setPage(state, page) {
      state.page = page;
    },
    setSelectedSort(state, selectedSort) {
      state.selectedSort = selectedSort;
    },
    setTotalPages(state, totalPages) {
      state.totalPages = totalPages;
    },
    setSearchQuery(state, searchQuery) {
      state.searchQuery = searchQuery;
    },
    setAddPost(state, newPost) {
      state.posts.unshift(newPost);
      // state.posts = [newPost, ...state.posts];
      state.posts.forEach((el, num) => {
        el.id = num;
      });
    },
  },
  getters: {
    sortedPosts(state) {
      return [...state.posts].sort((post1, post2) =>
        post1[state.selectedSort] > post2[state.selectedSort] ? 1 : -1
      );
    },
    sortedAndSearchedPosts(state, getters) {
      return getters.sortedPosts.filter((post) =>
        post.title.toLowerCase().includes(state.searchQuery.toLowerCase())
      );
    },
    sortOptions(state) {
      return state.sortOptions;
    },
  },
  // namespaced: true,
};
