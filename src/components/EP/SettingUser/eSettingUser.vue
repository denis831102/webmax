<template>
  <el-space wrap style="width: 100%">
    <el-card style="max-width: 380px; min-height: 130px">
      <template #header>
        <div class="card-header">
          <span
            >Оберіть стовбчик, в який Ви будете вносити дані в операції на
            власному пункті</span
          >
        </div>
      </template>
      <el-radio-group
        v-model="colOper"
        size="large"
        fill="#6cf"
        style="margin: 0px 0 0 5%"
        @change="changeCol"
      >
        <el-radio-button label="ціна одиниці (кг/т)" value="price" />
        <el-radio-button label="загальна сума" value="summa" />
      </el-radio-group>
    </el-card>

    <el-card style="max-width: 380px; min-height: 130px">
      <template #header>
        <div class="card-header">
          <span>Кількість транзакцій з операціями на листі</span>
        </div>
      </template>
      <el-input-number
        v-model="countTrans"
        :precision="0"
        :step="1"
        :min="1"
        size="large"
        style="width: 50%; height: 40px; margin: 15px 0 0 25%"
        @change="changeCount"
      />
    </el-card>

    <el-card style="min-width: 380px; min-height: 130px">
      <template #header>
        <div class="card-header">
          <span>Показувати системні сповіщення</span>
        </div>
      </template>
      <el-switch
        v-model="isShowMes"
        size="large"
        :active-action-icon="View"
        :inactive-action-icon="Hide"
        style="margin: 0px 0 20px 45%"
      />
    </el-card>

    <el-card style="min-width: 355px; min-height: 150px">
      <template #header>
        <div class="card-header">
          <span>Оберіть кольорову тему системи</span>
        </div>
      </template>
      <el-radio-group
        v-model="themaColor"
        size="large"
        fill="#6cf"
        style="margin: 10px 0 10px 10px"
        @change="changeThemaColor"
      >
        <el-radio-button label="світла" value="light" />
        <el-radio-button label="темна" value="dark" />
        <el-radio-button label="корпоративна" value="corporate" />
      </el-radio-group>
    </el-card>

    <el-card style="min-width: 380px; min-height: 130px">
      <template #header>
        <div class="card-header">
          <span>Налаштування 5</span>
        </div>
      </template>
    </el-card>

    <el-card style="min-width: 380px; min-height: 130px">
      <template #header>
        <div class="card-header">
          <span>Налаштування 6</span>
        </div>
      </template>
    </el-card>
  </el-space>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useStore } from "vuex";
import { Hide, View } from "@element-plus/icons-vue";

const store = useStore();
const getSettingUser = computed(() => store.getters.getSettingUser);
const changeSettingUser = (obj) => store.commit("changeSettingUser", obj);

const colOper = ref("summa");
const countTrans = ref(5);
const isShowMes = ref(true);
const themaColor = ref("light");

watch(
  () => isShowMes.value,
  () => changeShowMes()
);

const changeCol = () => {
  changeSettingUser({ colOper: colOper.value });
};

const changeCount = () => {
  changeSettingUser({ countTrans: countTrans.value });
};

const changeShowMes = () => {
  changeSettingUser({ isShowMes: isShowMes.value ? 1 : 0 });
};

const changeThemaColor = () => {
  changeSettingUser({ themaColor: themaColor.value });
  document.documentElement.classList.toggle("dark", themaColor.value == "dark");
  document.documentElement.setAttribute("data-theme", themaColor.value);
};

onMounted(() => {
  colOper.value = getSettingUser.value.colOper;
  themaColor.value = getSettingUser.value.themaColor;
  countTrans.value = getSettingUser.value.countTrans;
  isShowMes.value = +getSettingUser.value.isShowMes == 1;
});
</script>
