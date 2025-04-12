<template>
  <div class="cMain">
    <PostForm @create="createPost" class="cForm" :idPost="newId" />

    <post-list :posts="posts" @remove="removePost"></post-list>

    <div class="cForm">
      <h3>Робота плагіна</h3>
      <br />
      {{ $translate(title) }}
      <!-- {{ title }} -->
      <br /><br />
      {{ $word.pidkreslenja("hello frend, what is your name") }}
      <br /><br />
      {{ $word.hightRegistry(this.title) }}
    </div>
  </div>
</template>

<script>
import PostForm from "@/components/PostForm.vue";
import PostList from "@/components/PostList.vue";

export default {
  components: {
    PostList,
    PostForm,
    // PostForm: () => import("@/components/PostForm.vue"),
  },
  data() {
    return {
      posts: [
        { id: 1, title: "Java", body: "Дуже важливий опис 1" },
        { id: 2, title: "Javascript", body: "Дуже важливий опис 2" },
        { id: 3, title: "Phyton", body: "Дуже важливий опис 3" },
      ],
      title: "hello frend, what is your name",
      // body: "",
    };
  },
  methods: {
    createPost(newPost) {
      //   const newPost = {
      //     id: Date.now(),
      //     title: this.title,
      //     body: this.body,
      //   };
      this.posts.push(newPost);
      //   this.title = this.body = "";
    },
    removePost(post) {
      this.posts = this.posts.filter((p) => p.id !== post.id);
      // this.posts.splice(ind, 1);
    },
  },
  computed: {
    newId() {
      return this.posts.reduce(
        (maxId, curObj) => (curObj.id > maxId ? curObj.id : maxId),
        0
      );
      // return this.posts.length;
    },
    // newId() {
    //   return this.posts[this.posts.length - 1].id;
    // },
  },
};
</script>

<style scoped>
.cMain {
  display: flex;
  justify-content: flex-start;
}
.cForm {
  margin: 20px;
  align-self: center;
  border: 1px solid teal;
  padding: 10px 20px;
}
</style>
