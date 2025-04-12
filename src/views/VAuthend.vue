<template>
  <div>
    <eDialogAuthent v-model:visible="dialog['authet'].visible" />
  </div>
</template>

<script setup>
/* eslint-disable */

import "element-plus/dist/index.css";
import { ref } from "vue";
// import { ElNotification } from "element-plus";
import eDialogAuthent from "@/components/EP/eDialogAuthent";

const dialog = ref({
  authet: { visible: true },
});
</script>

<style scoped></style>
