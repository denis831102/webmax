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
        @change="changeCol"
        size="large"
        fill="#6cf"
        style="margin-top: 10px"
      >
        <el-radio-button label="ціна одиниці (кг/т)" value="price" />
        <el-radio-button label="загальна сума" value="summa" />
      </el-radio-group>
    </el-card>
  </el-space>
</template>
<script setup>
import { ref, onMounted, computed } from "vue";
import { useStore } from "vuex";

const colOper = ref("summa");
const store = useStore();
const getSettingUser = computed(() => store.getters.getSettingUser);
const changeSettingUser = (obj) => store.commit("changeSettingUser", obj);

const changeCol = () => {
  changeSettingUser({ colOper: colOper.value });
};

onMounted(() => {
  colOper.value = getSettingUser.value.colOper;
});
</script>
