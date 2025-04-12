<template>
  <div>
    <RouterLink to="/page4" class="link">
      <my-button>Повернутися до Сервіс v4 </my-button>
    </RouterLink>

    <h1>Сторінка поста з ID = {{ $route.params.id }}</h1>
  </div>
</template>

<script>
export default {};
</script>

<style scoped>
h1 {
  margin: 20px;
}
</style>
