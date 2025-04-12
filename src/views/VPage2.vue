<template>
  <!--  eslint-disable  -->
  <h1>Сервіс v2</h1>

  <my-input v-model="searchQuery" placeholder="Пошук в назві..." />

  <div class="app__btns">
    <my-button @click="fetchPosts(30)" style="align-self: flex-start">
      Отримати 30 постів
    </my-button>

    <my-button @click="showDialog" style="align-self: flex-start">
      Створити новий пост
    </my-button>

    <my-select v-model="selectedSort" :options="sortOptions"></my-select>
  </div>

  <my-dialog v-model:show="dialogVisible">
    <post-form @create="createPost" :idPost="posts[posts.length - 1].id" />
  </my-dialog>

  <!-- posts="posts"  sortedPosts -->
  <post-list
    :posts="sortedAndSearchedPosts"
    @remove="removePost"
    v-if="isPostLoading"
  />
  <h3 v-else>Завантажується...</h3>

  <!-- <div class="page__wrapper">
    <div
      v-for="pageNumber in totalPage"
      :key="pageNumber"
      class="page"
      :class="{
        'current-page': page === pageNumber,
      }"
      @click="changePage(pageNumber)"
    >
      {{ pageNumber }}
    </div>
  </div> -->

  <!-- <my-pagins
    v-model:totalPage="totalPage"
    v-model:page="page"
    @update="changePage"
  ></my-pagins> -->

  <my-pagins v-model:allPage="totalPage" v-model:curPage="page"></my-pagins>
</template>

<script>
import PostForm from "@/components/PostForm.vue";
import PostList from "@/components/PostList.vue";
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
      limit: 5,
      totalPage: 0,
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
        // this.posts = [];

        const response = await axios.get(
          //`https://jsonplaceholder.typicode.com/posts?_limit=${count}`
          `https://jsonplaceholder.typicode.com/posts`,
          {
            params: {
              _page: this.page,
              _limit: count ? count : this.limit,
            },
          }
        );

        this.posts = response.data;
        this.totalPage = Math.ceil(
          response.headers["x-total-count"] / this.limit
        );
        this.isPostLoading = true;
        // console.log(response);
      } catch (e) {
        alert("Помилка");
      }
    },
    changePage(pageNumber) {
      this.page = pageNumber;
    },
  },
  mounted() {
    this.fetchPosts();
  },
  watch: {
    //   selectedSort(newValue) {
    //     this.posts.sort((post1, post2) => {
    //       // return post1[newValue].localeCompare(post2[this.selectedSort]);
    //       return post1[newValue] > post2[newValue] ? 1 : -1;
    //     });
    //   },
    page() {
      this.fetchPosts();
    },
  },
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
  padding: 10px 20px;
  border: 1px solid teal;
}
.page__wrapper {
  display: flex;
  margin-top: 15px;
}
.current-page {
  border: 3px solid teal;
  background: #93a9af6b;
}
.page {
  border: 1px solid black;
  padding: 10px;
}
</style>
