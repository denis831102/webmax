<template>
  <!--  eslint-disable  -->
  <h1>Сервіс v5</h1>

  <h4>Admin (main): {{ $store.state.nameAuth }}</h4>
  <h4>Admin (modul): {{ $store.state.post.nameAuth }}</h4>

  <!-- <my-input v-focus v-model="searchQuery" placeholder="Пошук..." /> -->
  <my-input
    v-focus
    :model-value="searchQuery"
    @update:model-value="setSearchQuery"
    placeholder="Пошук..."
  />

  <div class="app__btns">
    <my-button @click="showDialog()" style="align-self: flex-start">
      Створити новий пост
    </my-button>

    <!-- <my-select v-model="selectedSort" :options="sortOptions"></my-select> -->
    <!-- :options="$store.state.post.sortOptions" -->
    <my-select
      :model-value="selectedSort"
      @update:model-value="setSelectedSort"
      :options="sortOptions"
    ></my-select>
  </div>

  <my-dialog-mix v-model:show="dialogVisible">
    <post-form @create="createPost" :countPosts="posts[posts.length - 1].id" />
  </my-dialog-mix>

  <div class="like__btns">
    <h2>likes - {{ $store.state.likes.likes }}</h2>
    <my-button @click="dialogVisibleLikes = true"> Форма лайків </my-button>
  </div>

  <!-- <div class="likes">
    <h1>кількість лайків - {{ getLikes }}</h1>
    <my-button @click="$store.commit('incrementLikes', 2)">Лайк</my-button>
    <my-button @click="decrementLikes(2)">Дізлайк</my-button>
  </div> -->

  <my-dialog-mix v-model:show="dialogVisibleLikes">
    <div class="likes">
      <h1>кількість лайків - {{ getLikes }}</h1>
      <!-- <h1>кількість лайків = {{ $store.getters.likes.getLikes }}</h1> -->
      <my-button @[eventName]="$store.commit('likes/incrementLikes', 2)"
        >Лайк</my-button
      >
      <!-- <my-button @click="$store.commit('decrementLikes', 2)">Дізлайк</my-button> -->
      <my-button @[eventName]="decrementLikes(2)">Дізлайк</my-button>
    </div>
  </my-dialog-mix>

  <post-list
    :posts="sortedAndSearchedPosts"
    @remove="removePost"
    v-if="isPostLoading"
  />
  <h3 v-else>Завантажується...</h3>

  <div v-intersection="loadMorePosts" class="observer"></div>
</template>

<script>
/* eslint-disable */
import PostForm from "@/components/PostForm4.vue";
import PostList from "@/components/PostList4.vue";
import { mapState, mapGetters, mapMutations, mapActions } from "vuex";
// import MyInputComp from "../components/UI/MyInputComp.vue";

export default {
  components: {
    PostList,
    PostForm,
  },
  data() {
    return {
      dialogVisible: false,
      dialogVisibleLikes: false,
    };
  },
  methods: {
    ...mapMutations({
      decrementLikes: "likes/decrementLikes",
    }),
    ...mapMutations([
      "setPage",
      "setSearchQuery",
      "setSelectedSort",
      "setAddPost",
    ]),
    ...mapActions({
      fetchPosts: "fetchPosts",
      loadMorePosts: "loadMorePosts",
      removePost: "removePost",
    }),
    createPost(newPost) {
      this.showDialog(false);
      this.setAddPost(newPost);
    },
    showDialog(bool = true) {
      this.dialogVisible = bool;
    },
  },
  mounted() {
    this.fetchPosts();
  },
  watch: {},
  computed: {
    ...mapState({
      posts: (state) => state.post.posts,
      isPostLoading: (state) => state.post.isPostLoading,
      selectedSort: (state) => state.post.selectedSort,
      searchQuery: (state) => state.post.searchQuery,
      // page: (state) => state.post.page,
      // limit: (state) => state.post.limit,
      // totalPages: (state) => state.post.totalPages,
    }),
    ...mapGetters({
      getLikes: "likes/getLikes",
    }),
    ...mapGetters(["sortedPosts", "sortedAndSearchedPosts", "sortOptions"]),
    eventName() {
      return this.getLikes >= 10 ? "click" : "dblclick";
    },
  },
};
</script>

<style scoped>
h1 {
  align-self: center;
  margin: 20px;
}
.app__btns {
  display: flex;
  justify-content: flex-start;
  margin: 15px 0px;
}
.page__wrapper {
  display: flex;
  margin-top: 15px;
}
.page {
  border: 1px solid black;
  padding: 10px;
}
.current-page {
  border: 3px solid teal;
  background: #93a9af6b;
}
.observer {
  height: 30px;
  /* background: green; */
}
.likes {
  display: flex;
  border: 2px dotted #0cde2d;
  margin: 20px 0;
}
.like__btns {
  display: flex;
  margin: 15px 0px;
  flex-direction: column;
  flex-wrap: wrap;
  align-content: center;
}
</style>
