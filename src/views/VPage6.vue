<template>
  <!--  eslint-disable  -->
  <h1>Сервіс v6</h1>

  <my-input v-focus v-model="searchQuery" placeholder="Пошук..." />

  <div class="app__btns">
    <my-button @click="showDialog" style="align-self: flex-start">
      Створити новий пост
    </my-button>

    <my-select v-model="selectedSort" :options="sortOptions"></my-select>
  </div>

  <my-dialog-mix v-model:show="dialogVisible">
    <post-form @create="createPost" :countPosts="posts[posts.length - 1].id" />
  </my-dialog-mix>

  <!-- :posts="sortedAndSearchedPosts" -->
  <post-list
    :posts="searchQuery.length ? newPost : sortedAndSearchedPosts"
    @remove="removePost"
    v-if="isPostLoading"
  />
  <h3 v-else>Завантажується...</h3>

  <!-- <div v-intersection="loadMorePosts" class="observer"></div> -->

  <!-- <h1>{{ likes }}</h1>
  <my-button @click="addLikes"> Лайк </my-button> -->
  <my-pagins
    v-model:allPage="totalPages"
    v-model:curPage="page.num"
  ></my-pagins>
</template>

<script>
/* eslint-disable */
import PostForm from "@/components/PostForm4.vue";
import PostList from "@/components/PostList4.vue";
import usePosts from "@/hooks/usePosts";
import { useSortedPosts } from "@/hooks/useSortedPosts";
import { useSortedAndSearchedPosts } from "@/hooks/useSortedAndSearchedPosts";

export default {
  components: {
    PostList,
    PostForm,
  },
  data() {
    return {
      sortOptions: [
        { value: "id", name: "За ID" },
        { value: "title", name: "За назвою" },
        { value: "body", name: "За змістом" },
      ],
    };
  },
  setup(props) {
    // const likes = ref(0);
    // const addLikes = () => {
    //   likes.value += 1;
    // };

    const {
      posts,
      isPostLoading,
      totalPages,
      removePost,
      dialogVisible,
      showDialog,
      createPost,
      page,
    } = usePosts(5);
    const { sortedPosts, selectedSort } = useSortedPosts(posts);
    const { sortedAndSearchedPosts, searchQuery, newPost } =
      useSortedAndSearchedPosts(sortedPosts);

    return {
      // likes,
      // addLikes,
      posts,
      isPostLoading,
      totalPages,
      removePost,
      showDialog,
      dialogVisible,
      createPost,
      page,

      sortedPosts,
      selectedSort,

      sortedAndSearchedPosts,
      searchQuery,
      newPost,

      // ...usePosts(5),
      // ...useSortedPosts(posts),
      // ...useSortedAndSearchedPosts(sortedPosts),
    };
  },
};
</script>

<!--  eslint-disable  -->
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

<style>
.search__text {
  background: rgb(199, 192, 95);
  padding: 2px 0;
}
</style>
