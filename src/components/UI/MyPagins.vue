<template>
  <div class="page__wrapper">
    <div
      v-for="pageNumber in allPage"
      :key="pageNumber"
      class="page"
      :class="{
        'current-page': curPage === pageNumber,
      }"
      @click="changePage(pageNumber)"
    >
      {{ pageNumber }}
    </div>
  </div>
</template>

<script>
export default {
  name: "my-pagins",
  props: {
    allPage: {
      type: Number,
      required: true,
    },
    curPage: {
      type: Number,
    },
  },
  methods: {
    changePage(pageNumber) {
      this.$emit("update:curPage", pageNumber);
      //   this.$emit("update", pageNumber);
    },
  },
};
</script>

<style scoped>
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
