<template>
  <el-space
    fill
    wrap
    fill-ratio="50"
    direction="horizontal"
    style="width: 100%"
  >
    <el-card style="max-width: 380px">
      <el-text class="mx-1"
        >Оберіть стовбчик, в який Ви будете вносити дані в операції на власному
        пункті</el-text
      >
      <el-radio-group
        v-model="colOper"
        size="large"
        fill="#6cf"
        style="margin-top: 15px"
        @change="changeCol"
      >
        <el-radio-button label="ціна одиниці (кг/т)" value="price" />
        <el-radio-button label="загальна сума" value="summa" />
      </el-radio-group>
    </el-card>

    <el-card style="max-width: 380px">
      <el-text class="mx-1">Кількість транзакцій з операціями на листі</el-text>
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
  </el-space>
</template>
<script setup>
import { ref, onMounted, computed } from "vue";
import { useStore } from "vuex";

const colOper = ref("summa");
const countTrans = ref(5);
const store = useStore();
const getSettingUser = computed(() => store.getters.getSettingUser);
const changeSettingUser = (obj) => store.commit("changeSettingUser", obj);

const changeCol = () => {
  changeSettingUser({ colOper: colOper.value });
};

const changeCount = () => {
  changeSettingUser({ countTrans: countTrans.value });
};

onMounted(() => {
  colOper.value = getSettingUser.value.colOper;
  countTrans.value = getSettingUser.value.countTrans;
});
</script>
