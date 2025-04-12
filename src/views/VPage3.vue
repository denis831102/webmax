<template>
  <!--  eslint-disable  -->

  <!-- <h1>Сервіс постів v3</h1> -->
  <my-heading :level="1">Сервіс v3</my-heading>

  <!-- <my-input v-model="searchQuery" placeholder="Пошук..." /> -->
  <my-input-comp
    :value="searchQuery"
    @input="debouncedChange"
    @keyup.end="clearSearch"
    placeholder="Пошук в назві..."
  />

  <div class="app__btns">
    <my-button @click="showDialog" style="align-self: flex-start">
      Створити новий пост
    </my-button>

    <my-select v-model="selectedSort" :options="sortOptions"></my-select>
  </div>

  <my-dialog v-model:show="dialogVisible">
    <post-form @create="createPost" :idPost="posts[posts.length - 1].id" />
  </my-dialog>

  <keep-alive> <component :is="curComponent" /> </keep-alive>

  <!-- <transition name="fade"> -->
  <transition name="slide-fade">
    <post-list
      v-if="isPostLoading"
      :posts="sortedAndSearchedPosts"
      @remove="removePost"
    />
    <my-loader v-else class="loader" />
    <!-- <h3 v-else>Завантажується...</h3> -->
  </transition>

  <div ref="observer" class="observer"></div>
</template>

<script>
/* eslint-disable */

import PostForm from "@/components/PostForm.vue";
import PostList from "@/components/PostList.vue";
import axios from "axios";
import * as _ from "lodash";

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
        // this.isPostLoading = false;
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

        return new Promise((resolve) => {
          setTimeout(() => {
            resolve(response);
          }, 2000);
        })
          .then((response) => {
            this.posts = response.data;
          })
          .finally(() => {
            this.isPostLoading = true;
          });
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
    changeInp(event) {
      this.searchQuery = event.target.value;
    },
    clearSearch() {
      this.searchQuery = "";
    },
  },
  mounted() {
    this.fetchPosts();

    const options = {
      rootMargin: "0px",
      threshold: 1.0,
    };
    const callback = (entries, observer) => {
      if (entries[0].isIntersecting && this.page < this.totalPages) {
        this.loadMorePosts();
      }
    };
    const iObserver = new IntersectionObserver(callback, options);
    iObserver.observe(this.$refs.observer);
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
    curComponent() {
      return this.isPostLoading ? "my-button" : "my-loader";
    },
  },
  created() {
    // debounce за допомогою Lodash
    this.debouncedChange = _.debounce(this.changeInp, 1000);
  },
  unmounted() {
    // зупинка debounce (таймера) після знишення компонента
    this.debouncedChange.cancel();
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
  padding: 10px 20px;
  border: 1px solid teal;
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

.loader {
  margin: auto;
  display: flex;
  transform: scale(2);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 1.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(20px);
  opacity: 0;
}
</style>
