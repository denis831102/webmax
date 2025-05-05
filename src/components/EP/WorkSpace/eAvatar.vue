<template>
  <el-image class="demo-image block" :src="circleUrl" fit="contain" />
</template>

<script setup>
/* eslint-disable */
import { reactive, toRefs } from "vue";

const state = reactive({
  circleUrl: "https://webmax.lond.lg.ua/php/img/caption_2_2.png",
  sizeList: ["small", "", "large"],
});

const { circleUrl, sizeList } = toRefs(state);
</script>

<style scoped>
.demo-image.block {
  padding: 30px 0;
  text-align: center;
  border-right: solid 1px var(--el-border-color);
  display: inline-block;
  box-sizing: border-box;
  vertical-align: top;
  align-items: center;
  opacity: 0.8;
  width: 80%;
  height: 90%;
  margin-left: 10%;
}
.demo-image.block:last-child {
  border-right: none;
}
.demo-image.demonstration {
  display: block;
  color: var(--el-text-color-secondary);
  font-size: 14px;
  margin-bottom: 20px;
  align-items: center;
}
</style>
