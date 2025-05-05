<template>
  <el-space :size="10" style="margin: 0 0 10px 0">
    <el-card>
      <el-space :size="10">
        <el-switch v-model="isPeriod" @change="getTransaction" />
        <el-date-picker
          v-model="valueDate"
          type="daterange"
          format="DD.MM.YYYY"
          :start-placeholder="getDate"
          :end-placeholder="getDate"
          :disabled="!isPeriod"
          @change="getTransaction"
          style="width: 230px"
        />
      </el-space>
    </el-card>

    <el-card>
      <el-space :size="10">
        <el-button-group class="ml-4">
          <el-button
            type="primary"
            plain
            :icon="Refresh"
            @click="getMonitoring()"
          >
            Оновити
          </el-button>
        </el-button-group>
      </el-space>
    </el-card>
  </el-space>

  <el-table :data="filterTable">
    <el-table-column type="index" />

    <el-table-column label="Менеджер" prop="date">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Calendar /></el-icon>
          <span style="margin-left: 10px"
            >{{ scope.row.date }} - {{ scope.row.time }}</span
          >
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Дані">
      <el-progress type="dashboard" :percentage="percentage" :color="colors" />
    </el-table-column>
  </el-table>
</template>

<script setup>
import { inject, ref, computed, onActivated } from "vue";
import { useStore } from "vuex";
import { Calendar } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const valueDate = ref([new Date(), new Date()]);
const isPeriod = ref(false);
const curDate = ref(new Date());

const percentage = ref(10);
const colors = [
  { color: "#f56c6c", percentage: 20 },
  { color: "#e6a23c", percentage: 40 },
  { color: "#5cb87a", percentage: 60 },
  { color: "#1989fa", percentage: 80 },
  { color: "#6f7ad3", percentage: 100 },
];

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabAnalitika"];
  return _tabl.data;
});

const getMonitoring = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getMonitoring",
        _id_U: getCurUser.value.id,
        _date_l: formatDate(valueDate.value[0], "eng"),
        _date_r: formatDate(valueDate.value[1], "eng"),
        _isPeriod: isPeriod.value ? 1 : 0,
      },
    });

    setting.value.tables["tabAnalitika"].data = response.data.ar_data;

    ElMessage.success("Аналітика оновлена");
  } catch (e) {
    ElMessage.error("Помилка завантаження аналітики");
  }
};

const getDate = computed(() => {
  return formatDate(curDate.value);
});

const formatDate = (valDate, mode = "ukr") => {
  const date = {
    d: valDate.getDate(),
    m: valDate.getMonth() + 1,
    y: valDate.getFullYear(),
  };
  return mode == "ukr"
    ? [
        (date.d < 10 ? "0" : "") + date.d,
        (date.m < 10 ? "0" : "") + date.m,
        date.y,
      ].join(".")
    : [
        date.y,
        (date.m < 10 ? "0" : "") + date.m,
        (date.d < 10 ? "0" : "") + date.d,
      ].join("-");
};

onActivated(async () => {
  await getMonitoring();
});
</script>

<style>
.demo-progress .el-progress--line {
  margin-bottom: 15px;
  max-width: 600px;
}
.demo-progress .el-progress--circle {
  margin-right: 15px;
}
</style>
