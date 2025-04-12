<template>
  <div>
    <el-radio-group v-model="isCollapse" style="margin-bottom: 20px">
      <el-radio-button :value="false">
        <el-icon><View /></el-icon>
      </el-radio-button>
      <el-radio-button :value="true">
        <el-icon><CaretLeft /></el-icon>
      </el-radio-button>
    </el-radio-group>

    <el-menu
      default-active="2"
      class="el-menu-vertical-demo"
      :collapse="isCollapse"
      @open="handleOpen"
      @close="handleClose"
      @select="handleSelect"
    >
      <el-sub-menu index="1">
        <template #title>
          <el-icon><User /></el-icon>
          <span>Користувачі</span>
        </template>
        <el-menu-item-group>
          <template #title><span>Користувачі системи </span></template>
          <el-menu-item index="1-1">Список користувачів</el-menu-item>
          <el-menu-item index="1-2">Статуси</el-menu-item>
        </el-menu-item-group>
        <el-menu-item-group title="Редагування">
          <el-menu-item index="1-3">Форма додавання</el-menu-item>
        </el-menu-item-group>
        <el-sub-menu index="1-4">
          <template #title><span>item four</span></template>
          <el-menu-item index="1-4-1">item 1</el-menu-item>
          <el-menu-item index="1-4-2">item 2</el-menu-item>
        </el-sub-menu>
      </el-sub-menu>
      <el-menu-item index="2">
        <el-icon><icon-menu /></el-icon>
        <template #title>Операції</template>
      </el-menu-item>
      <el-menu-item index="3" disabled>
        <el-icon><document /></el-icon>
        <template #title>Звіти</template>
      </el-menu-item>
      <el-menu-item index="4">
        <el-icon><Setting /></el-icon>
        <template #title>Налаштування</template>
      </el-menu-item>
    </el-menu>
  </div>
</template>

<script setup>
/* eslint-disable */

import { ref, defineProps, inject } from "vue";
import {
  //   Document,
  Menu as IconMenu,
  //   Location,
  Setting,
} from "@element-plus/icons-vue";

const props = defineProps({
  open: Function,
  // modelValue: Number,
});

const setting = inject("setting");

const isCollapse = ref(true);

const handleOpen = (key, keyPath) => {
  // console.log(key, keyPath);
};

const handleClose = (key, keyPath) => {
  // console.log(key, keyPath);
};

const handleSelect = (key, keyPath) => {
  console.log(key);
  switch (key) {
    case "4":
      props.open({
        text: `${key} ${keyPath}`,
      });
      break;
    case "1-1":
      // emit("update:modelValue", 0);
      setting.value.curComp = 0;
      break;
    case "1-2":
      // emit("update:modelValue", 1);
      setting.value.curComp = 1;
      break;
    case "1-3":
      setting.value.dialog["user"].visible = true;
      break;
  }
};
</script>

<style>
.el-menu-vertical-demo:not(.el-menu--collapse) {
  width: 200px;
  min-height: 400px;
}
</style>
