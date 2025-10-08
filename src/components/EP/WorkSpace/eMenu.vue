<template>
  <div>
    <el-radio-group
      v-if="props.mode == 'vertical'"
      v-model="isCollapse"
      style="margin-bottom: 20px"
    >
      <el-radio-button :value="false">
        <el-icon><View /></el-icon>
      </el-radio-button>
      <el-radio-button :value="true">
        <el-icon><CaretLeft /></el-icon>
      </el-radio-button>
    </el-radio-group>

    <el-menu
      default-active="2-1"
      :class="classMenu"
      :collapse="isCollapse"
      :mode="props.mode"
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
                ><el-icon><ToiletPaper /></el-icon> Номенклатура</span
              ></template
            >
            <el-menu-item index="1-4-1"
              ><el-icon><List /></el-icon>Категорії номенклатури</el-menu-item
            >
            <el-menu-item index="1-4-2"
              ><el-icon><Ticket /></el-icon>Види номенклатури</el-menu-item
            >
          </el-sub-menu>
        </el-menu-item-group>
        <el-sub-menu index="1-5">
          <template #title
            ><span>
              <el-icon><Avatar /></el-icon> Контрагенти</span
            ></template
          >
          <el-menu-item index="1-5-1"
            ><el-icon><ShoppingCart /></el-icon> Покупці
          </el-menu-item>
          <el-menu-item index="1-5-2" :disabled="true">
            <el-icon><Edit /></el-icon>Компонент ( в розробці)
          </el-menu-item>
        </el-sub-menu>
      </el-sub-menu>

      <el-sub-menu index="2">
        <template #title>
          <el-icon><Grid /></el-icon>
          <span>Операції</span>
        </template>
        <el-menu-item-group>
          <el-menu-item index="2-1">
            <el-icon><Briefcase /></el-icon>Залишки на ВП
          </el-menu-item>
          <el-menu-item index="2-2">
            <el-icon><CircleCheck /></el-icon>
            Операції по ВП
          </el-menu-item>
          <el-menu-item index="2-3">
            <el-icon><EditPen /></el-icon>
            Загальні залишки на ВП
          </el-menu-item>
        </el-menu-item-group>
      </el-sub-menu>

      <el-sub-menu index="3" :disabled="isDisabled_Monitoring">
        <template #title>
          <el-icon><document /></el-icon>
          <span>Моніторинг</span>
        </template>
        <el-menu-item-group>
          <el-menu-item index="3-1" :disabled="isDisabled_Analitika_1">
            <el-icon><TrendCharts /></el-icon>
            Залишки по номенклатурі на власних пунктах
          </el-menu-item>
          <el-menu-item index="3-2" :disabled="!+getCurUser.listAccess[7]">
            <el-icon><Money /></el-icon>
            Залишки по коштам на власних пунктах
          </el-menu-item>
          <el-menu-item index="3-3" :disabled="isDisabled_Analitika_2">
            <el-icon><Guide /></el-icon>
            Рух коштів на власних пунктах
          </el-menu-item>
          <el-menu-item index="3-4" :disabled="isDisabled_Analitika_1">
            <el-icon><Document /></el-icon>
            Щоденний звіт економіста
          </el-menu-item>
        </el-menu-item-group>
      </el-sub-menu>

      <el-menu-item index="4">
        <el-icon><Setting /></el-icon>
        <template #title>Налаштування</template>
      </el-menu-item>

      <el-menu-item index="5">
        <el-icon><CloseBold /></el-icon>
        <template #title>Вихід</template>
      </el-menu-item>
    </el-menu>
  </div>
</template>

<script setup>
/* eslint-disable */

import { ref, defineProps, inject, computed, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";

const setting = inject("setting");
const router = useRouter();
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const changeAuthenticated = (val) => store.commit("changeAuthenticated", val);
const isCollapse = ref();
const props = defineProps({
  mode: Boolean,
});
const classMenu = reactive({
  "el-menu-vertical-demo": props.mode == "vertical",
  "el-menu-horizontal-demo": props.mode == "horizontal",
});

const handleOpen = (key, keyPath) => {
  console.log(key, keyPath);
};

const handleClose = (key, keyPath) => {
  console.log(key, keyPath);
};

const handleSelect = (key, keyPath) => {
  switch (key) {
    case "1-1":
      setting.value.comps.curComp = "eTable_User";
      setting.value.titleTable = setting.value.tables["tabUser"].title;
      break;
    case "1-2":
      setting.value.comps.curComp = "eTable_Status";
      setting.value.titleTable = setting.value.tables["tabStatus"].title;
      break;
    case "1-3":
      setting.value.comps.curComp = "eTable_Punkt";
      setting.value.titleTable = setting.value.tables["tabPunkt"].title;
      break;
    case "1-4-1":
      setting.value.comps.curComp = "eTable_Kategories";
      setting.value.titleTable = setting.value.tables["tabKategories"].title;
      break;
    case "1-4-2":
      setting.value.comps.curComp = "eTable_Material";
      setting.value.titleTable = setting.value.tables["tabMaterial"].title;
      break;
    case "1-5-1":
      setting.value.comps.curComp = "eTable_Buyers";
      setting.value.titleTable = setting.value.tables["tabBuyers"].title;
      break;
    case "2-1":
      setting.value.comps.curComp = "eBits";
      setting.value.titleTable = setting.value.tables["tabBits"].title;
      break;
    case "2-2":
      setting.value.comps.curComp = "eOperation";
      setting.value.titleTable = setting.value.tables["tabTransaction"].title;
      break;
    case "2-3":
      setting.value.comps.curComp = "eAnalitikaM";
      setting.value.titleTable = setting.value.tables["tabAnalitikaM"].title;
      break;
    case "3-1":
      setting.value.comps.curComp = "eAnalitika";
      setting.value.titleTable = setting.value.tables["tabAnalitika"].title;
      break;
    case "3-3":
      setting.value.comps.curComp = "eMoney";
      setting.value.titleTable = setting.value.tables["tabMoney"].title;
      break;
    case "3-4":
      setting.value.comps.curComp = "eEconomist";
      setting.value.titleTable = setting.value.tables["tabEconomist"].title;
      break;
    case "3-2":
      setting.value.comps.curComp = "eZvitMoney";
      setting.value.titleTable = setting.value.tables["tabZvitMoney"].title;
      break;

    case "4":
      setting.value.comps.curComp = "eSettingUser";
      setting.value.titleTable = "НАЛАШТУВАННЯ КОРИСТУВАЧА";
      break;
    case "5":
      changeAuthenticated(false);
      router.push({ name: "authent" });
      break;

    default: {
      setting.value.comps.curComp = "eAvatar";
      setting.value.titleTable = "";
    }
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

const isDisabled_Monitoring = computed(() => {
  return !+getCurUser.value.listAccess[3] && !+getCurUser.value.listAccess[4];
});
const isDisabled_Analitika_1 = computed(() => {
  return !+getCurUser.value.listAccess[3];
});
const isDisabled_Analitika_2 = computed(() => {
  return !+getCurUser.value.listAccess[4];
});
const isDisabled_Analitika_3 = computed(() => {
  return !+getCurUser.value.listAccess[4];
});

onMounted(() => {
  isCollapse.value = props.mode == "vertical";
});
</script>

<style>
.el-menu-vertical-demo:not(.el-menu--collapse) {
  width: 250px;
  min-height: 400px;
}
.el-menu-horizontal-demo {
  width: 100%;
  height: 40px;
}
</style>
