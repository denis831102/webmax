<template>
  <div
    :class="{
      dark: getSettingUser.themaColor == 'black',
      'app-container': getSettingUser.themaColor == 'black',
    }"
  >
    <!-- @click.prevent="$router.push({ name: 'crm' }) -->
    <div class="hMain" @click="fullScreen()" :title="versionDate">
      Web MetAl'ans <span class="version"> v.{{ version }} </span>
    </div>

    <transition name="component-fade" mode="out-in">
      <RouterView class="sRout" />
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useStore } from "vuex";

// eslint-disable-next-line no-undef
// const version = __APP_VERSION__;
const version = process.env.VUE_APP_VERSION;
const versionDate = process.env.VUE_APP_VERSION_DATE;
const modeScreen = ref(false);

const store = useStore();
const getSettingUser = computed(() => store.getters.getSettingUser);

const fullScreen = () => {
  let elem = document.documentElement;
  modeScreen.value = !modeScreen.value;
  if (modeScreen.value) {
    if (elem.requestFullscreen) {
      elem.requestFullscreen();
    } else if (elem.webkitRequestFullscreen) {
      // для Safari
      elem.webkitRequestFullscreen();
    } else if (elem.msRequestFullscreen) {
      // для IE11
      elem.msRequestFullscreen();
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      // для Safari
      document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
      // для IE11
      document.msExitFullscreen();
    }
  }
};
</script>

<style>
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  color: #2c3e50;
  margin: 10px;
}
* {
  margin: 0;
  /* padding: 0; */
  box-sizing: border-box;
}
.hMain {
  padding: 10px;
  background: linear-gradient(rgba(0, 0, 255, 0.4), rgba(255, 255, 0, 0.4));
  /* background: #ecf5ff; */
  box-shadow: 1px 0px 9px 3px #568989;
  text-align: center;
  font-size: 20pt;
}

.logo {
  width: 20px;
  height: 20px;
}

.version {
  font-size: 14pt;
  color: #7589d2;
}

.sRout {
  display: flex;
  /* border: 1px solid teal; */
  margin: 10px 0;
  background: #f4f7f7;
  padding: 20px 0;
}

.component-fade-enter-active,
.component-fade-leave-active {
  transition: opacity 0.3s ease;
}

.component-fade-enter-from,
.component-fade-leave-to {
  opacity: 0;
}

/* ------------------ Общие стили ------------------ */
.app-container {
  background-color: var(--app-bg);
  color: var(--app-text);

  /* плавный переход для фона и текста */
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* el-card с плавным переходом */
.el-card {
  background-color: var(--card-bg);
  color: var(--app-text);
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* el-button: плавный переход для фона, текста и бордера */
.el-button {
  transition: background-color 0.3s ease, color 0.3s ease,
    border-color 0.3s ease;
}

/* ------------------ Светлая тема ------------------ */
:root {
  --el-color-primary: #1e90ff;
  --el-color-success: #4caf50;
  --el-color-warning: #ff9800;
  --el-color-danger: #f44336;
  --el-color-info: #2196f3;
  --el-bg-color-table: #fff;
  --el-text-color-primary: #333;

  --app-bg: #f5f5f5;
  --app-text: #333;
  --card-bg: #fff;
  --tabs-active: #1e90ff;
}

/* ------------------ Тёмная тема ------------------ */
.dark {
  --el-color-primary: #673ab7;
  --el-color-success: #4caf50;
  --el-color-warning: #ff9800;
  --el-color-danger: #e91e63;
  --el-color-info: #00bcd4;
  --el-bg-color-table: #2c2c2c;
  --el-text-color-primary: #f0f0f0;

  --app-bg: #1e1e1ecf;
  --app-text: #f0f0f0;
  --card-bg: #2c2c2ced;
  --tabs-active: #673ab7;
}

/* Дополнительно можно менять строки */
.dark .el-table th,
.dark .el-table td {
  background-color: var(--el-bg-color-table);
  color: var(--el-text-color-primary);
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Чередование полос */
/* .dark .el-table .el-table__row:nth-child(even) {
  background-color: #816161;
}
.dark .el-table .el-table__row:nth-child(odd) {
  background-color: #339595;
} */

/* ------------------ Стили для el-tabs ------------------ */
.el-tabs__header {
  background-color: var(--tabs-bg);
  transition: background-color 0.3s ease;
}

.el-tabs__item {
  color: var(--tabs-text);
  transition: color 0.3s ease, background-color 0.3s ease;
}

.el-tabs__item.is-active {
  color: var(--tabs-active);
  font-weight: bold;
}

.el-tabs__content {
  background-color: var(--tabs-bg);
  color: var(--tabs-text);
  padding: 15px;
  transition: background-color 0.3s ease, color 0.3s ease;
}
</style>
