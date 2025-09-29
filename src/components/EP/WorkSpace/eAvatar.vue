<template>
  <div>
    <transition name="fade-zoom">
      <el-image v-if="showImage" class="demo-image block" :src="circleUrl" />
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const circleUrl = ref("https://webmax.lond.lg.ua/php/img/caption_2.3.jpg");
const showImage = ref(false);

onMounted(() => {
  // задержка для плавного появления
  setTimeout(() => {
    showImage.value = true;
  }, 100);
});
</script>

<style scoped>
.demo-image.block {
  padding: 30px 0;
  text-align: center;
  border-right: solid 1px var(--el-border-color);
  display: inline-block;
  vertical-align: top;
  align-items: center;
  opacity: 0.8;
  width: 100%;
  margin-top: -55px;
}

/* спецэффект */
.fade-zoom-enter-active {
  animation: fadeZoomIn 1s ease forwards;
}
.fade-zoom-leave-active {
  animation: fadeZoomOut 0.5s ease forwards;
}

@keyframes fadeZoomIn {
  0% {
    opacity: 0;
    transform: scale(0.2);
  }
  100% {
    opacity: 0.8;
    transform: scale(1);
  }
}

@keyframes fadeZoomOut {
  0% {
    opacity: 0.8;
    transform: scale(1);
  }
  100% {
    opacity: 0;
    transform: scale(3);
  }
}
</style>
