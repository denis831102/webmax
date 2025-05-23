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

  <el-card>
    <div class="demo-progress">
      <el-row :gutter="20">
        <el-col :span="4"
          ><el-tag type="primary" style="width: 100%"
            >Параметр 1</el-tag
          ></el-col
        >
        <el-col :span="20"
          ><el-progress :text-inside="true" :stroke-width="26" :percentage="70"
        /></el-col>
      </el-row>

      <el-row :gutter="20">
        <el-col :span="4"
          ><el-tag type="primary" style="width: 100%"
            >Параметр 2</el-tag
          ></el-col
        >
        <el-col :span="20"
          ><el-progress
            :text-inside="true"
            :stroke-width="24"
            :percentage="100"
            status="success"
        /></el-col>
      </el-row>

      <el-row :gutter="20">
        <el-col :span="4"
          ><el-tag type="primary" style="width: 100%"
            >Параметр 3</el-tag
          ></el-col
        >
        <el-col :span="20"
          ><el-progress
            :text-inside="true"
            :stroke-width="22"
            :percentage="80"
            status="warning"
        /></el-col>
      </el-row>

      <el-row :gutter="20">
        <el-col :span="4"
          ><el-tag type="primary" style="width: 100%"
            >Параметр 4</el-tag
          ></el-col
        >
        <el-col :span="20">
          <el-progress
            :text-inside="true"
            :stroke-width="20"
            :percentage="50"
            status="exception"
        /></el-col>
      </el-row>
    </div>
  </el-card>

  <el-table :data="filterTable">
    <el-table-column type="index" />

    <el-table-column label="Менеджер" prop="pib" width="250">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.pib }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Дані">
      <template #default="scope">
        <el-space :size="40">
          <el-progress
            type="dashboard"
            :percentage="scope.row.percentage_1"
            :color="colors"
          />
          <el-progress
            type="dashboard"
            :percentage="scope.row.percentage_2"
            :color="colors"
          />
        </el-space>
      </template>
    </el-table-column>

    <el-table-column label="Деталізація">
      <el-descriptions
        title="Деталізація"
        direction="vertical"
        :column="4"
        size="default"
        border
      >
        <el-descriptions-item label="Дані 1">Значення 1</el-descriptions-item>
        <el-descriptions-item label="Дані 2">Значення 2</el-descriptions-item>
        <el-descriptions-item label="Дані 3" :span="2"
          >Значення 3</el-descriptions-item
        >
        <el-descriptions-item label="Дані 4">
          <el-tag size="small">Значення 4</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="Дані 4"> Значення 5 </el-descriptions-item>
      </el-descriptions>
    </el-table-column>
  </el-table>
</template>

<script setup>
import { inject, ref, computed, onActivated, watch } from "vue";
import { useStore } from "vuex";
import { User } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const valueDate = ref([new Date(), new Date()]);
const isPeriod = ref(false);
const curDate = ref(new Date());
const colors = [
  { color: "#f56c6c", percentage: 20 },
  { color: "#e6a23c", percentage: 40 },
  { color: "#5cb87a", percentage: 60 },
  { color: "#1989fa", percentage: 80 },
  { color: "#6f7ad3", percentage: 100 },
];
const checkAll = ref(false);
const indeterminate = ref(false);
const checkManeger = ref([]);
const listManeger = ref([]);

watch(checkManeger, (val) => {
  if (val.length === 0) {
    checkAll.value = false;
    indeterminate.value = false;
  } else if (val.length === listManeger.value.length) {
    checkAll.value = true;
    indeterminate.value = false;
  } else {
    indeterminate.value = true;
  }
});

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabAnalitika"];
  return _tabl.data;
});

const getMonitoring = async () => {
  try {
    const response = await HTTP.post("", {
      _method: "getMonitoring",
      _id_U: getCurUser.value.id,
      _date_l: formatDate(valueDate.value[0], "eng"),
      _date_r: formatDate(valueDate.value[1], "eng"),
      _isPeriod: isPeriod.value ? 1 : 0,
      _checkManeger: checkManeger.value,
    });

    setting.value.tables["tabAnalitika"].data = response.data.ar_data;

    ElMessage.success("Аналітика оновлена");
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

    ElMessage.success("Список менеджерів оновлений");
  } catch (e) {
    ElMessage.error("Помилка завантаження менеджеров");
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

const handleCheckAll = (val) => {
  indeterminate.value = false;
  if (val) {
    checkManeger.value = listManeger.value.map((_) => _.value);
  } else {
    checkManeger.value = [];
  }
};

onActivated(async () => {
  await getManeger();
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
.custom-header {
  display: flex;
  height: unset;
}
</style>
