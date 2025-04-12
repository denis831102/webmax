<template>
  <!--  eslint-disable  -->
  <div v-if="posts.length">
    <h3>Список постів (кіл-ь: {{ posts.length }})</h3>

    <transition-group name="post-list">
      <post-item
        v-for="post in posts"
        :post="post"
        :key="post.id"
        @remove="$emit('remove', post)"
      >
      </post-item>
    </transition-group>
  </div>
  <h2 v-else style="color: red; margin: 20px">Список постів пустий</h2>
</template>

<script>
// eslint-disable

import PostItem from "@/components/PostItem";

export default {
  components: {
    PostItem,
  },
  props: {
    posts: {
      type: Array,
      required: true,
    },
  },
};
</script>

<style scoped>
.post-list-item {
  display: inline-block;
  margin-right: 10px;
}
.post-list-enter-active,
.post-list-leave-active {
  transition: all 0.4s ease;
}
.post-list-enter-from,
.post-list-leave-to {
  opacity: 0;
  transform: translateX(230px);
}
.post-list-move {
  transition: transform 0.8s ease;
}

h3 {
  margin-top: 20px;
}
.red__text {
  color: red;
}
</style>
