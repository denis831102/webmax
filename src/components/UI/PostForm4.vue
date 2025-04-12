<template>
  <form @submit.prevent="createPost">
    <h4>Створення поста!</h4>
    <my-input v-focus v-model="post.title" placeholder="Назва" />
    <my-input v-model="post.body" placeholder="Опис" />
    <my-button class="btn">Створити</my-button>
  </form>
</template>

<script>
export default {
  props: {
    countPosts: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      post: {
        title: "",
        body: "",
      },
    };
  },
  methods: {
    createPost() {
      this.post.id = +this.countPosts + 1;
      this.$emit("create", this.post);
      this.post = {
        title: "",
        body: "",
      };
    },
  },
  // watch: {
  //   post: {
  //     handler(newVal) {
  //       console.log(newVal);
  //     },
  //     deep: true,
  //   },
  // },
};
</script>

<style>
form {
  display: flex;
  flex-direction: column;
}
</style>
