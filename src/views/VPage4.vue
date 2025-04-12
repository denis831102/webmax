<template>
  <!--  eslint-disable  -->
  <h1>Сервіс v4</h1>

  <my-input v-focus v-model="searchQuery" placeholder="Пошук у назві..." />

  <div class="app__btns">
    <my-button @click="showDialog" style="align-self: flex-start">
      Створити новий пост
    </my-button>

    <my-select v-model="selectedSort" :options="sortOptions"></my-select>
  </div>

  <my-dialog-mix v-model:show="dialogVisible">
    <post-form @create="createPost" :countPosts="posts[posts.length - 1].id" />
  </my-dialog-mix>

  <post-list
    :posts="sortedAndSearchedPosts"
    @remove="removePost"
    v-if="isPostLoading"
  />
  <h3 v-else>Завантажується...</h3>

  <div v-intersection:[textDir]="loadMorePosts" class="observer"></div>
</template>

<script>
/* eslint-disable */

import PostForm from "@/components/PostForm4.vue";
import PostList from "@/components/PostList4.vue";
import axios from "axios";

export default {
  components: {
    PostList,
    PostForm,
  },
  data() {
    return {
      posts: [],
      dialogVisible: false,
      isPostLoading: false,
      selectedSort: "id",
      searchQuery: "",
      page: 1,
      limit: 10,
      textDir: ["my-directive", "obj"],
      totalPages: 0,
      sortOptions: [
        { value: "id", name: "За ID" },
        { value: "title", name: "За назвою" },
        { value: "body", name: "За змістом" },
      ],
    };
  },
  methods: {
    createPost(newPost) {
      this.posts.push(newPost);
      this.dialogVisible = false;
    },
    removePost(post) {
      this.posts = this.posts.filter((p) => p.id !== post.id);
    },
    showDialog() {
      this.dialogVisible = true;
    },
    async fetchPosts(count) {
      try {
        this.isPostLoading = true;
        const response = await axios.get(
          `https://jsonplaceholder.typicode.com/posts`,
          {
            params: {
              _page: this.page,
              _limit: count ? count : this.limit,
            },
          }
        );

        this.totalPages = Math.ceil(
          response.headers["x-total-count"] / this.limit
        );
        this.posts = response.data;
      } catch (e) {
        alert("Помилка");
      }
    },
    async loadMorePosts() {
      try {
        this.page += 1;

        this.isPostLoading = true;
        const response = await axios.get(
          `https://jsonplaceholder.typicode.com/posts`,
          {
            params: {
              _page: this.page,
              _limit: this.limit,
            },
          }
        );

        this.totalPages = Math.ceil(
          response.headers["x-total-count"] / this.limit
        );
        this.posts = [...this.posts, ...response.data];
      } catch (e) {
        alert("Помилка авто завантаження");
      }
    },
  },
  mounted() {
    this.fetchPosts();
  },
  watch: {},
  computed: {
    sortedPosts() {
      return [...this.posts].sort((post1, post2) =>
        post1[this.selectedSort] > post2[this.selectedSort] ? 1 : -1
      );
    },
    sortedAndSearchedPosts() {
      return this.sortedPosts.filter((post) =>
        post.title.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
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
</style>
