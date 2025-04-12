<template>
  <el-avatar :size="sizeList[2]" :src="circleUrl" />
</template>

<script setup>
import { reactive, toRefs } from "vue";

const state = reactive({
  circleUrl:
    "https://cube.elemecdn.com/3/7c/3ea6beec64369c2642b92c6726f1epng.png",
  sizeList: ["small", "", "large"],
});

const { circleUrl, sizeList } = toRefs(state);
</script>
