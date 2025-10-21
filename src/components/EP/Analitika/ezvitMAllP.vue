<template>
  <el-space style="margin: 0 0 10px 0">
    <div class="card">
      <el-row :gutter="10" wrap>
        <!-- Фільтр (перемикач) -->
        <el-col :xs="4" :sm="12" :md="2" :lg="2">
          <el-switch
            v-model="isFilter"
            @change="getAllKasa"
            style="float: right"
          />
        </el-col>

        <!-- Дата -->
        <el-col :xs="20" :sm="12" :md="6" :lg="6">
          <el-date-picker
            v-model="curDate"
            type="date"
            format="DD.MM.YYYY"
            :start-placeholder="getDate"
            :end-placeholder="getDate"
            :disabled="!isFilter"
            size="normal"
            style="width: 100%"
            @change="getAllKasa"
          />
        </el-col>

        <!-- Виводити/невиводити "0" номенклатури -->
        <el-col :xs="24" :sm="12" :md="8" :lg="8">
          <el-radio-group v-model="withoutZeroKasa" style="width: 100%">
            <el-radio-button value="allKas" label="Всі" />
            <el-radio-button value="withoutZero" label="Наявні" />
          </el-radio-group>
        </el-col>

        <!-- Оновити -->
        <el-col :xs="24" :sm="12" :md="8" :lg="8">
          <el-button
            type="primary"
            plain
            :icon="Refresh"
            @click="getAllKasa"
            style="width: 100%"
          >
            Оновити
          </el-button>
        </el-col>
      </el-row>
    </div>
  </el-space>

  <el-table :data="filterTable" row-style="background:#f4f4f5">
    <el-table-column>
      <template #default="scope">
        <div>
          <!-- <el-icon><User /></el-icon> -->
          <!-- <span style="margin-left: 10px">{{ scope.row.pib }}</span> -->
          <el-tag
            type="success"
            size="large"
            style="margin-left: 50%; font-size: large"
          >
            <el-icon><Money /></el-icon>
            {{ parseFloat(scope.row.summa_U).toLocaleString("rus") }}</el-tag
          >
        </div>
        <el-table :data="scope.row.listPunkt" style="width: 95%">
          <el-table-column type="index" />
          <el-table-column prop="namePunkt" />
          <el-table-column min-width="100">
            <template #default="props">
              <div style="padding: 5px 0 5px 10px; background: #c6e2ff69">
                <span style="margin-left: 10px"
                  >{{ parseFloat(props.row.summa_K).toLocaleString("ua") }}
                </span>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
/* eslint-disable */
//отключает наблюдатель за ошибками

import { inject, ref, computed, onActivated } from "vue";
import { useStore } from "vuex";
import { User, Refresh } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
const loading = ref(true);
const curDate = ref(new Date());
const withoutZeroKasa = ref("withoutZero");
const isFilter = ref(false);

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

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabzvitMAllP"].data;
  return _tabl.map((user) => {
    return {
      ...user,
      listPunkt: user.listPunkt.filter(
        (punkt) =>
          (withoutZeroKasa.value == "withoutZero" &&
            Math.abs(punkt.summa_K) > 0.99999) ||
          withoutZeroKasa.value == "allKas"
      ),
    };
  });

  //   const _tabl = {
  //     data: [
  //       {
  //         id_U: "30",
  //         pib: "Ратова Євгенія",
  //         listPunkt: [
  //           {
  //             id: "11",
  //             namePunkt: "ДМС_ЦМ",
  //             id_K: "6",
  //             summa_K: 500,
  //           },
  //           {
  //             id: "12",
  //             namePunkt: "отрадный",
  //             id_K: "6",
  //             summa_K: 830,
  //           },
  //         ],
  //         summa_U: 1330,
  //       },
  //     ],
  //   };
  //   return _tabl.data;
});

const getAllKasa = async () => {
  //   loading.value = true;
  try {
    const response = await HTTP.post("", {
      _method: "getAllKasa",
      _id_U: getCurUser.value.id,
      _checkManeger: [getCurUser.value.id],
      _date: isFilter.value ? formatDate(curDate.value, "eng") : "",
    });

    setting.value.tables["tabzvitMAllP"].data = response.data.ar_data;
    // loading.value = false;

    if (+getSettingUser.value.isShowMes) {
      let limitDate = isFilter.value
        ? formatDate(curDate.value, "ukr")
        : formatDate(new Date(), "ukr");
      ElMessage.success(`Аналітика по касам на ${limitDate} оновлена`);
    }
  } catch (e) {
    ElMessage.error("Помилка завантаження аналітики");
  }
};
onActivated(async () => {
  await getAllKasa();
});
</script>

<style scoped></style>
