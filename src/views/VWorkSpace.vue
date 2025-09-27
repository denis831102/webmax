<template>
  <!-- eslint-disable -->
  <el-row>
    <el-col :span="24">
      <!-- <eMenu mode="horizontal" style="margin-top: -20px" /> -->

      <el-card style="margin-top: -25px">
        <el-container>
          <el-aside width="250" v-if="setting.displaySize == 'large'">
            <eMenu mode="vertical" />
          </el-aside>

          <el-container>
            <el-header style="margin-top: -15px">
              <el-row :gutter="20">
                <el-col :span="24">
                  <eMenu mode="horizontal" />
                </el-col>
              </el-row>

              <el-row :gutter="20" style="text-align: center">
                <el-col :span="19">
                  <el-text v-if="setting.titleTable.length" tag="b"
                    >{{ setting.titleTable }}
                  </el-text>
                </el-col>
                <el-col :span="5" style="text-align: right">
                  <eDrop_User :user="getCurUser" />
                </el-col>
              </el-row>
            </el-header>

            <el-main class="box-main">
              <keep-alive> <component :is="curComponent" /> </keep-alive>
            </el-main>

            <el-footer class="box-footer">R@ED 2025</el-footer>
          </el-container>
        </el-container>
      </el-card>
    </el-col>
  </el-row>
</template>

<script setup>
// import "element-plus/dist/index.css";
import { ref, computed, provide, onMounted } from "vue";
import { useStore } from "vuex";
// import { ElNotification } from "element-plus";
// import { Search } from "@element-plus/icons-vue";

import eMenu from "@/components/EP/WorkSpace/eMenu";
import eDrop_User from "@/components/EP/WorkSpace/eDrop_User";
import eAvatar from "@/components/EP/WorkSpace/eAvatar";

import eTable_User from "@/components/EP/Users/eTable_User";
import eTable_Status from "@/components/EP/Status/eTable_Status";
import eTable_Punkt from "@/components/EP/Punkt/eTable_Punkt";
import eTable_Material from "@/components/EP/Material/eTable_Material";
import eTable_Kategories from "@/components/EP/Kategories/eTable_Kategories";
import eTable_Buyers from "@/components/EP/Buyers/eTable_Buyers";
import eOperation from "@/components/EP/Operation/eOperation";
import eBits from "@/components/EP/Bits/eBits";
import eAnalitika from "@/components/EP/Monitoring/eAnalitika";
import eAnalitikaM from "@/components/EP/Analitika/eAnalitikaM";
import eEconomist from "@/components/EP/Monitoring/eEconomist";
import eMoney from "@/components/EP/Monitoring/eMoney";

import eSettingUser from "@/components/EP/SettingUser/eSettingUser";

const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
// const isLeftMenu = ref(true);
// const mode = ref("vertical");
// const open = (obj) => {
//   ElNotification({
//     title: "Info",
//     message: obj.text,
//     type: "info",
//   });
// };

const setting = ref({
  name: "Денис і Євгенія Ратови",
  titleTable: "",
  displaySize: "large",
  isDark: false,
  dialog: {
    user: { visible: false },
    editUser: { visible: false, initiator: "" },
    editManeger: {
      visible: false,
      initiator: "",
      idManeger: 0,
      chooseUser: "user",
    },
    editStatus: { visible: false, initiator: "" },
    editPunkt: { visible: false, initiator: "" },
    editOperation: { visible: false, initiator: "" },
    editMaterial: { visible: false, initiator: "" },
    editBuyers: { visible: false, initiator: "" },
    editBits: { visible: false, initiator: "" },
    createPeresort: { visible: false, initiator: "" },
  },
  comps: {
    list: {
      eAvatar,
      eTable_User,
      eTable_Status,
      eTable_Punkt,
      eTable_Material,
      eTable_Kategories,
      eTable_Buyers,
      eOperation,
      eBits,
      eAnalitika,
      eAnalitikaM,
      eEconomist,
      eMoney,
      eSettingUser,
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
      title: "ДОВІДНИК ВЛАСНИХ ПУНКТІВ",
      curRow: {},
      data: [],
    },
    tabMaterial: {
      title: "ДОВІДНИК НОМЕНКЛАТУРИ",
      curRow: {},
      data: [],
    },
    tabKategories: {
      title: "КАТЕГОРІЇ НОМЕНКЛАТУРИ",
      curRow: {},
      data: [],
    },
    tabBuyers: {
      title: "ПОКУПЦІ",
      curRow: {},
      data: [],
    },
    tabBits: {
      title: "ЗАЛИШКИ НА ВЛАСНИМ ПУНКТАХ",
      curRow: {},
      data: [],
    },
    tabTransaction: {
      title: "ОПЕРАЦІЇ МЕНЕДЖЕРА",
      curRow: {},
      data: [],
    },
    tabAnalitika: {
      title: "АНАЛІТИКА ПО НОМЕНКЛАТУРІ НА ВЛАСНИХ ПУНКТАХ",
      curRow: {},
      data: [],
    },
    tabAnalitikaM: {
      title: "ЗАГАЛЬНІ ЗАЛИШКИ",
      curRow: {},
      data: [],
    },
    tabEconomist: {
      title: "Щоденний звіт по ВЛАСНИМ ПУНКТАМ",
      curRow: {},
      data: [],
    },
    tabMoney: {
      title: "АНАЛІТИКА ПО РУХУ ГРОШОВИХ КОШТІВ ВЛАСНИХ ПУНКТІВ",
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
  setting.value.displaySize =
    document.documentElement.clientWidth >= 1242 ? "large" : "small";
};

onMounted(() => {
  window.addEventListener("resize", onResize);
  onResize();
  // fullScreen();
});
</script>

<style scoped>
.box-main {
  height: calc(100vh - 198px);
  overflow: auto;
  width: 100%;
  margin-top: calc(2vh + 15px);
  padding: 0px;
}

.box-footer {
  text-align: center;
  margin-top: 10px;
  height: 5px;
}
</style>
