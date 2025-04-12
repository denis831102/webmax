<template>
  <div class="post">
    <div>
      <div>
        ID: <strong>{{ post.id }}</strong>
      </div>
      <div><strong>Назва:</strong> {{ post.title }}</div>
      <div><strong>Зміст:</strong> {{ post.body }}</div>
    </div>
    <div class="post__btns">
      <my-button @click="$emit('remove', post)">Видалити </my-button>
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
  background: white;
}
.post:hover {
  background: #6c887e20;
}
.post__btns {
  margin: 5px 10px;
}
</style>
