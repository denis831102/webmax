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
      <el-sub-menu index="1" :disabled="isDisabled_UserStatus">
        <template #title>
          <el-icon><Collection /></el-icon>
          <span>Довідники</span>
        </template>
        <el-menu-item-group>
          <el-menu-item index="1-1" :disabled="isDisabled_User"
            ><el-icon><User /></el-icon>
            Користувачі
          </el-menu-item>
          <el-menu-item index="1-2" :disabled="isDisabled_Status"
            ><el-icon><CircleCheck /></el-icon>
            Статуси
          </el-menu-item>
          <el-menu-item index="1-3"
            ><el-icon><HomeFilled /></el-icon>Власні пункти
          </el-menu-item>
          <el-sub-menu index="1-4">
            <template #title
              ><span
                ><el-icon><ToiletPaper /></el-icon> Матеріали</span
              ></template
            >
            <el-menu-item index="1-4-1"
              ><el-icon><List /></el-icon>Категорії</el-menu-item
            >
            <el-menu-item index="1-4-2"
              ><el-icon><Ticket /></el-icon>Види матеріалів</el-menu-item
            >
          </el-sub-menu>
        </el-menu-item-group>
        <el-sub-menu index="1-5">
          <template #title
            ><span
              ><el-icon><SetUp /></el-icon>Додатково</span
            ></template
          >
          <el-menu-item index="1-5-1"
            ><el-icon><EditPen /></el-icon>Операція 1</el-menu-item
          >
          <el-menu-item index="1-5-2"
            ><el-icon><Edit /></el-icon>Операція 2</el-menu-item
          >
        </el-sub-menu>
      </el-sub-menu>
      <el-menu-item index="2">
        <el-icon><Briefcase /></el-icon>
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

import { ref, defineProps, inject, computed, onMounted } from "vue";
import {
  Menu as IconMenu,
  Setting,
  Grid,
  HomeFilled,
  Briefcase,
} from "@element-plus/icons-vue";
import { useStore } from "vuex";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const isCollapse = ref(true);

const props = defineProps({
  open: Function,
});

const handleOpen = (key, keyPath) => {
  // console.log(key, keyPath);
};

const handleClose = (key, keyPath) => {
  // console.log(key, keyPath);
};

const handleSelect = (key, keyPath) => {
  console.log(key);
  switch (key) {
    case "1-1":
      // emit("update:modelValue", 1);
      setting.value.comps.curComp = 1;
      break;
    case "1-2":
      setting.value.comps.curComp = 2;
      break;
    case "1-5-1":
      setting.value.dialog["user"].visible = true;
      break;
    case "2":
      setting.value.comps.curComp = 3;
      break;
    case "4":
      props.open({
        text: `${key} ${keyPath}`,
      });
      break;
    default:
      setting.value.comps.curComp = 0;
  }
};

const isDisabled_UserStatus = computed(() => {
  return !+getCurUser.value.listAccess[1] && !+getCurUser.value.listAccess[2];
});
const isDisabled_User = computed(() => {
  return !+getCurUser.value.listAccess[1];
});
const isDisabled_Status = computed(() => {
  return !+getCurUser.value.listAccess[2];
});
</script>

<style>
.el-menu-vertical-demo:not(.el-menu--collapse) {
  width: 200px;
  min-height: 400px;
}
</style>
