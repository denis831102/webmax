<template>
  <el-card style="max-width: 380px">
    <el-text class="mx-1"
      >Оберіть стовбчик, в який Ви будете вносити дані</el-text
    >
    <el-radio-group
      v-model="colOper"
      @change="changeCol"
      size="large"
      fill="#6cf"
      style="margin-top: 10px"
    >
      <el-radio-button label="загальна сума" value="summa" />
      <el-radio-button label="вартість одиниці (кг/т)" value="price" />
    </el-radio-group>
  </el-card>
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
