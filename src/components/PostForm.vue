<template>
  <!--  eslint-disable  -->
  <form @submit.prevent="createPost">
    <h3>Створення поста</h3>
    <my-input v-model.capitalize="post.title" placeholder="Назва" />
    <my-input v-model.number="post.body" placeholder="Опис" />
    <my-button class="btn">Створити</my-button>
  </form>
</template>

<script>
// import MyButton from "@/UI/MyButton";
// :value="post.title"  @input="post.title = $event.target.value"

export default {
  //components: { MyButton },
  // name: "MainDialogForm",
  props: {
    idPost: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      post: {
        id: 0,
        title: "",
        body: "",
      },
    };
  },
  methods: {
    createPost() {
      // this.post.id = Date.now();
      this.post.id = +this.idPost + 1;

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
  border: 1px solid teal;
  padding: 5px 20px;
  background: white;
}
</style>
