<template>
  <div>
    <eDialog_Authent v-model:visible="dialog['authet'].visible" />
  </div>
</template>

<script setup>
/* eslint-disable */

import { ref } from "vue";
// import { ElNotification } from "element-plus";
import eDialog_Authent from "@/components/EP/Load/eDialog_Authent";

const dialog = ref({
  authet: { visible: true },
});
</script>

<style scoped></style>
