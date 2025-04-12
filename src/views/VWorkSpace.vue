<template>
  <!-- eslint-disable -->
  <!-- <el-button plain @click="open"> Success </el-button> -->
  <el-row :gutter="10">
    <el-col :span="24">
      <eDialog
        :dialogText="setting.dialog.main.text"
        v-model:visible="setting.dialog['main'].visible"
        :funcOk="open"
        :argFunc="arg"
      />

      <eDialogEdit v-model:visible="setting.dialog['edit'].visible" />

      <eDialogUser v-model:visible="setting.dialog['user'].visible" />

      <eMenuG />

      <el-card class="box-card" style="max-width: 50%; margin-top: 20px">
        <el-button
          :icon="Search"
          plain
          type="primary"
          @click="
            setting.dialog.main.visible = true;
            setting.dialog.main.text = 'Перевірка кнопки';
          "
        >
          Відкрити діалог
        </el-button>
      </el-card>

      <el-card
        class="box-card common-layout"
        style="min-height: 500px; width: 100%; margin-top: 20px"
      >
        <el-container class="layout-container-demo" style="min-height: 500px">
          <el-aside width="50">
            <!-- v-model="setting.curId" -->
            <eMenuV :open="open" />
          </el-aside>

          <el-container>
            <el-header style="text-align: right; font-size: 12px">
              <eUserDrop :name="getCurUser.PIB" />
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
import eMenuV from "@/components/EP/eMenuV";
import eMenuG from "@/components/EP/eMenuG";
import eUserDrop from "@/components/EP/eUserDrop";
import eDialog from "@/components/EP/eDialog";
import eDialogEdit from "@/components/EP/eDialogEdit";
import eDialogUser from "@/components/EP/eDialogUser";
import eTable_1 from "@/components/EP/eTable_1";
import eTable_2 from "@/components/EP/eTable_2";

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
    edit: { visible: false },
    user: { visible: false },
  },
  compTable: [eTable_1, eTable_2],
  curComp: 1,
  numRec: 0,
  tableData: [
    {
      date: "2025-03-03",
      name: "Ігор",
      address: "No. 189, Grove St, Los Angeles",
    },
    {
      date: "2025-03-02",
      name: "Петро",
      address: "No. 189, Grove St, Los Angeles",
    },
    {
      date: "2025-03-04",
      name: "Василь",
      address: "No. 189, Grove St, Los Angeles",
    },
    {
      date: "2025-03-01",
      name: "Олег",
      address: "No. 189, Grove St, Los Angeles",
    },
  ],
});

const curComponent = computed(() => {
  return setting.value.compTable[setting.value.curComp];
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
