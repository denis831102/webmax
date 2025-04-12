<template>
  <div class="post">
    <div>
      <div>
        <strong>ID:</strong> <strong>{{ post.id }}</strong>
      </div>
      <div>
        <strong>Назва: </strong>
        <!-- <span>{{ post.title }}</span> -->
        <span v-html="post.title"></span>
      </div>
      <div><strong>Зміст:</strong> {{ post.body }}</div>
    </div>
    <div class="post__btns">
      <!-- <my-button @click="$router.push(`/page4/${post.id}`)">Відкрити</my-button> -->
      <my-button
        @click="$router.push({ name: 'page4i', params: { id: post.id } })"
        >Відкрити</my-button
      >
      <!-- <my-button
        @click="$router.push(`{name: 'page4', params: {id: ${post.id}}`)"
        >Відкрити</my-button
      > -->
      <my-button @click="$emit('remove', post)">Видалити </my-button>

      <slot :curpost="post"></slot>
      /
      {{ postsAll.length }}
      {{ nameAvtor }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    post: {
      type: Object,
      required: true,
    },
  },
  inject: ["postsAll", "nameAvtor"],
};
</script>

<style scoped>
.post {
  padding: 15px;
  border: 2px solid teal;
  margin-top: 15px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.post:hover {
  background: #6c887e20;
}
.post__btns {
  display: flex;
}
</style>
