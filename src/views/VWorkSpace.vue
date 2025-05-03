<template>
  <!-- eslint-disable -->
  <el-row :gutter="10">
    <el-col :span="24">
      <eMenu mode="horizontal" style="margin-top: -20px" />

      <el-card
        class="box-card common-layout"
        style="min-height: 500px; width: 100%; margin-top: 0px"
      >
        <el-container class="layout-container-demo" style="min-height: 500px">
          <el-aside width="250" v-if="isLeftMenu">
            <eMenu mode="vertical" />
          </el-aside>

          <el-container>
            <el-header style="text-align: right; font-size: 12px">
              <el-row :gutter="20">
                <el-col :span="18" style="text-align: center">
                  <el-text v-if="setting.titleTable.length" tag="b"
                    >{{ setting.titleTable }}
                  </el-text>
                </el-col>
                <el-col :span="6">
                  <eDrop_User :user="getCurUser" />
                </el-col>
              </el-row>
            </el-header>

            <el-main>
              <keep-alive> <component :is="curComponent" /> </keep-alive>
            </el-main>

            <el-footer height="30" style="margin-top: 20px"
              >R@ED 2025</el-footer
            >
          </el-container>
        </el-container>
      </el-card>
    </el-col>
  </el-row>
</template>

<script setup>
/* eslint-disable */

import "element-plus/dist/index.css";
import {
  ref,
  computed,
  provide,
  onBeforeMount,
  onMounted,
  onBeforeUnmount,
} from "vue";
import { useStore } from "vuex";
import { ElNotification } from "element-plus";
import { Search } from "@element-plus/icons-vue";

import eMenu from "@/components/EP/WorkSpace/eMenu";
import eDrop_User from "@/components/EP/WorkSpace/eDrop_User";
import eAvatar from "@/components/EP/WorkSpace/eAvatar";

import eTable_User from "@/components/EP/Users/eTable_User";
import eTable_Status from "@/components/EP/Status/eTable_Status";
import eTable_Punkt from "@/components/EP/Punkt/eTable_Punkt";
import eOperation from "@/components/EP/Operation/eOperation";
import eBits from "@/components/EP/Bits/eBits";
import eTable_Material from "@/components/EP/Material/eTable_Material";
import eTable_Kategories from "@/components/EP/Kategories/eTable_Kategories";

const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const isLeftMenu = ref(true);
// const mode = ref("vertical");
// const open = (obj) => {
//   ElNotification({
//     title: "Info",
//     message: obj.text,
//     type: "info",
//   });
// };

const setting = ref({
  name: "Денис Ратов",
  titleTable: "",
  dialog: {
    user: { visible: false },
    editUser: { visible: false, initiator: "" },
    editStatus: { visible: false, initiator: "" },
    editPunkt: { visible: false, initiator: "" },
    editOperation: { visible: false, initiator: "" },
    editMaterial: { visible: false, initiator: "" },
    editBits: { visible: false, initiator: "" },
  },
  comps: {
    list: {
      eAvatar,
      eTable_User,
      eTable_Status,
      eTable_Punkt,
      eTable_Material,
      eTable_Kategories,
      eOperation,
      eBits,
    },
    curComp: "eAvatar",
  },
  tables: {
    tabUser: {
      title: "КОРИСТУВАЧІ СИСТЕМИ",
      curRow: {},
      data: [],
    },
    tabStatus: {
      title: "СТАТУСИ КОРИСТУВАЧІВ",
      curRow: {},
      data: [],
    },
    tabPunkt: {
      title: "ВЛАСНІ ПУНКТИ",
      curRow: {},
      data: [],
    },
    tabMaterial: {
      title: "НОМЕНКЛАТУРА",
      curRow: {},
      data: [],
    },
    tabKategories: {
      title: "КАТЕГОРІЇ НОМЕНКЛАТУРИ",
      curRow: {},
      data: [],
    },
    tabBits: {
      title: "ЗАЛИШКИ НА ПУНКТАХ",
      curRow: {},
      data: [],
    },
    tabTransaction: {
      title: "ОПЕРАЦІЇ МЕНЕДЖЕРА",
      curRow: {},
      data: [],
    },
  },
});

provide("setting", setting);

const curComponent = computed(() => {
  let comps = setting.value.comps;
  return comps.list[comps.curComp];
});

const onResize = () => {
  isLeftMenu.value = document.documentElement.clientWidth >= 500;
  // isLeftMenu.value = window.innerWidth >= 500;
};

onMounted(() => {
  window.addEventListener("resize", onResize);
});
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
