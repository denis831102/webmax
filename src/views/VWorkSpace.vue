<template>
  <!-- eslint-disable -->
  <el-row :gutter="10">
    <el-col :span="24">
      <eDialog
        :dialogText="setting.dialog.main.text"
        v-model:visible="setting.dialog['main'].visible"
        :funcOk="open"
        :argFunc="arg"
      />

      <eDialog_Edit v-model:visible="setting.dialog['edit'].visible" />

      <eDialog_User v-model:visible="setting.dialog['user'].visible" />

      <eDialog_Status v-model:visible="setting.dialog['editStatus'].visible" />

      <eMenu_G />

      <el-card
        v-if="false"
        class="box-card"
        style="max-width: 50%; margin-top: 20px"
      >
        <el-button
          :icon="Search"
          plain
          type="primary"
          @click="
            setting.dialog.main.visible = true;
            setting.dialog.main.text = 'Перевірка кнопки';
          "
        >
          Відкрити тестувальний діалог
        </el-button>
      </el-card>

      <el-card
        class="box-card common-layout"
        style="min-height: 500px; width: 100%; margin-top: 20px"
      >
        <el-container class="layout-container-demo" style="min-height: 500px">
          <el-aside width="50">
            <!-- v-model="setting.curId" -->
            <eMenu_V :open="open" />
          </el-aside>

          <el-container>
            <el-header style="text-align: right; font-size: 12px">
              <eDrop_User :user="getCurUser" />
            </el-header>

            <el-main>
              <keep-alive> <component :is="curComponent" /> </keep-alive>
            </el-main>

            <el-footer height="30" style="margin-top: 20px">R@D 2025</el-footer>
          </el-container>
        </el-container>
      </el-card>
    </el-col>
  </el-row>
</template>

<script setup>
/* eslint-disable */

import "element-plus/dist/index.css";
import { ref, computed, provide } from "vue";
import { useStore } from "vuex";
import { ElNotification } from "element-plus";
import { Search } from "@element-plus/icons-vue";

import eMenu_V from "@/components/EP/WorkSpace/eMenu_V";
import eMenu_G from "@/components/EP/WorkSpace/eMenu_G";
import eDrop_User from "@/components/EP/WorkSpace/eDrop_User";
import eAvatar from "@/components/EP/WorkSpace/eAvatar";

import eDialog from "@/components/EP/Load/eDialog";

import eDialog_Edit from "@/components/EP/Users/eDialog_Edit";
import eDialog_User from "@/components/EP/Users/eDialog_User";
import eTable_User from "@/components/EP/Users/eTable_User";

import eTable_Status from "@/components/EP/Status/eTable_Status";
import eDialog_Status from "@/components/EP/Status/eDialog_Status";

import eTable_Operation from "@/components/EP/Operation/eTable_Operation";

const arg = ref({
  text: "222",
});

const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);

const open = (obj) => {
  ElNotification({
    title: "Info",
    message: obj.text,
    type: "info",
  });
};

const setting = ref({
  name: "Денис Ратов",
  dialog: {
    main: { visible: false, text: "" },
    edit: { visible: false, initiator: "" },
    user: { visible: false },
    editStatus: { visible: false },
  },
  comps: {
    list: [eAvatar, eTable_User, eTable_Status, eTable_Operation],
    curComp: 0,
  },
  tables: {
    tabUser: {
      numRec: 0,
      curRow: {},
      data: [],
    },
    tabStatus: {
      numRec: 1,
      data: [],
    },
    tabResurs: {
      numRec: 2,
      data: [],
    },
  },
});

const curComponent = computed(() => {
  let comps = setting.value.comps;
  return comps.list[comps.curComp];
});

provide("setting", setting);
</script>

<style scoped>
.layout-container-demo .el-header {
  position: relative;
  /* background-color: var(--el-color-primary-light-9); */
  color: var(--el-text-color-primary);
}
.layout-container-demo .el-aside {
  color: var(--el-text-color-primary);
  /* background: var(--el-color-primary-light-8); */
}
.layout-container-demo .el-menu {
  border-right: none;
}
.layout-container-demo .el-main {
  padding: 0;
}
.layout-container-demo .toolbar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  right: 20px;
}
</style>
