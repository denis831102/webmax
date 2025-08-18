<template>
  <el-space :size="10" style="margin: 0 0 10px 0">
    <el-card>
      <el-space :size="10" wrap>
        <el-select
          v-model="checkManeger"
          multiple
          clearable
          collapse-tags
          placeholder="оберіть менеджера..."
          popper-class="custom-header"
          :max-collapse-tags="1"
          style="width: 240px"
          @change="getMonitoring"
        >
          <template #header>
            <el-checkbox
              v-model="checkAll"
              :indeterminate="indeterminate"
              @change="handleCheckAll"
            >
              Усі
            </el-checkbox>
          </template>
          <el-option
            v-for="item in listManeger"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </el-space>
    </el-card>
  </el-space>
</template>

<script setup>
import { inject, ref, computed, onActivated } from "vue";
import { useStore } from "vuex";
// import { User } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
// const colors = [
//   { color: "#ff55ff", percentage: 10 },
//   { color: "#f56c6c", percentage: 20 },
//   { color: "#e6a23c", percentage: 40 },
//   { color: "#5cb87a", percentage: 60 },
//   { color: "#1989fa", percentage: 80 },
//   { color: "#6f7ad3", percentage: 100 },
// ];
const checkAll = ref(false);
const indeterminate = ref(false);
const checkManeger = ref([]);
const checkMaterial = ref([]);
const listManeger = ref([]);
// const options = ref([]);
const loading = ref(true);
// const propsCascader = { multiple: true, expandTrigger: "hover" };

const getMonitoring = async () => {
  loading.value = true;
  try {
    const response = await HTTP.post("", {
      _method: "getMonitoring",
      _id_U: getCurUser.value.id,
      _checkManeger: checkManeger.value,
      _checkMaterial: checkMaterial.value,
    });

    setting.value.tables["tabAnalitika"].data = response.data.ar_data;
    loading.value = false;

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Аналітика оновлена");
    }
  } catch (e) {
    ElMessage.error("Помилка завантаження аналітики");
  }
};

const getManeger = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getManeger",
        _id_U: getCurUser.value.id,
      },
    });

    listManeger.value = response.data.ar_data;

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Список менеджерів оновлений");
    }
  } catch (e) {
    ElMessage.error("Помилка завантаження менеджеров");
  }
};

onActivated(async () => {
  await getManeger();
  //   await getKategories();
  //   await getMonitoring();
});
</script>

<style></style>
