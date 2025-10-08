<template>
  <el-space style="margin: 0 0 10px 0">
    <div class="card">
      <el-row :gutter="10" wrap>
        <!-- Менеджер -->
        <el-col :xs="24" :sm="12" :md="8" :lg="5">
          <el-select
            v-model="checkManeger"
            multiple
            clearable
            collapse-tags
            placeholder="оберіть менеджера..."
            popper-class="custom-header"
            :max-collapse-tags="1"
            style="width: 100%"
            @change="getAllKasa()"
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
        </el-col>

        <!-- Фільтр (перемикач) -->
        <el-col :xs="4" :sm="12" :md="5" :lg="2">
          <el-switch
            v-model="isFilter"
            @change="getAllKasa"
            style="float: right"
          />
        </el-col>

        <!-- Дата -->
        <el-col :xs="8" :sm="12" :md="8" :lg="4">
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
        <el-col :xs="12" :sm="12" :md="8" :lg="5">
          <el-radio-group v-model="withoutZeroKasa" style="width: auto">
            <el-radio-button value="allKas" label="Всі" />
            <el-radio-button value="withoutZero" label="Наявні" />
          </el-radio-group>
        </el-col>

        <!-- Завантажити -->
        <el-col :xs="24" :sm="12" :md="8" :lg="5">
          <input type="file" ref="fileInput" style="display: none" />

          <el-button
            type="success"
            :icon="Tickets"
            @click="openFile"
            style="width: 100%"
            :disabled="!+getCurUser.listAccess[11]"
          >
            Завантажити аналітику
          </el-button>
        </el-col>

        <!-- Оновити -->
        <el-col :xs="24" :sm="24" :md="8" :lg="3">
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
    <el-table-column type="index" v-if="setting.displaySize == 'large'" />

    <el-table-column width="200" v-if="setting.displaySize == 'large'">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.pib }}</span>
        </div>
        <div style="margin: 10px 0 10px 15px">
          <el-tag type="success" size="large" style="font-size: large">
            <el-icon><Money /></el-icon>
            {{ parseFloat(scope.row.summa_U).toLocaleString("rus") }}</el-tag
          >
        </div>
      </template>
    </el-table-column>

    <el-table-column>
      <template #default="scope">
        <div
          v-if="setting.displaySize == 'small'"
          style="display: flex; align-items: center; margin: 0 0 10px 15px"
        >
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.pib }}</span>

          <el-tag
            type="success"
            size="large"
            style="margin-left: 50px; font-size: large"
          >
            <el-icon><Money /></el-icon>
            {{ parseFloat(scope.row.summa_U).toLocaleString("rus") }}</el-tag
          >
        </div>

        <el-table :data="scope.row.listPunkt" style="width: 95%">
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
import { inject, ref, computed, onActivated, watch } from "vue";
import { useStore } from "vuex";
import { User, Refresh, Tickets } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();

const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);

const checkAll = ref(false);
const indeterminate = ref(false);
const checkManeger = ref([]);
const listManeger = ref([]);
// const options = ref([]);
const loading = ref(true);
// const propsCascader = { multiple: true, expandTrigger: "hover" };
const isFilter = ref(false);
const curDate = ref(new Date());
// const fileInput = ref(null);
const withoutZeroKasa = ref("withoutZero");

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
  const _tabl = setting.value.tables["tabZvitMoney"].data;
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

  // const _tabl = {
  //   data: [
  //     {
  //       id_U: "30",
  //       pib: "Ратова Євгенія",
  //       listPunkt: [
  //         {
  //           id: "11",
  //           namePunkt: "ДМС_ЦМ",
  //           id_K: "6",
  //           summa_K: 500,
  //         },
  //         {
  //           id: "12",
  //           namePunkt: "отрадный",
  //           id_K: "6",
  //           summa_K: 830,
  //         },
  //       ],
  //     },
  //   ],
  // };
  // return _tabl.data;
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

const getAllKasa = async () => {
  loading.value = true;
  try {
    const response = await HTTP.post("", {
      _method: "getAllKasa",
      _id_U: getCurUser.value.id,
      _checkManeger: checkManeger.value,
      _date: isFilter.value ? formatDate(curDate.value, "eng") : "",
    });

    setting.value.tables["tabZvitMoney"].data = response.data.ar_data;
    loading.value = false;

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

const handleCheckAll = (val) => {
  indeterminate.value = false;
  if (val) {
    checkManeger.value = listManeger.value.map((_) => _.value);
  } else {
    checkManeger.value = [];
  }
  getAllKasa();
};

// const onFileSelected = (event) => {
//   const files = event.target.files;
//   if (!files || !files.length) return;
//   loadReport(files[0]);
// };

// const openFile = () => {
//   if (fileInput.value) fileInput.value.value = "";
//   fileInput.value && fileInput.value.click();
// };

// const loadReport = async (file) => {
//   try {
//     const formData = new FormData();
//     formData.append("_file", file);
//     formData.append("_method", "loadReport");
//     formData.append("_nameReport", "analitikaResurs");
//     formData.append("_id_U", getCurUser.value.id);
//     formData.append("_checkManeger", checkManeger.value);
//     formData.append("_checkMaterial", checkMaterial.value);
//     formData.append(
//       "_date",
//       isFilter.value ? formatDate(curDate.value, "eng") : ""
//     );

//     const response = await HTTP.post("", formData);

//     if (response.data.isSuccesfull) {
//       loadFile(
//         response.data.fileName,
//         response.data.content,
//         response.data.mime
//       );

//       ElMessageBox({
//         title: "Увага!",
//         type: "success",
//         dangerouslyUseHTMLString: true,
//         message: response.data.message,
//       });
//     } else {
//       ElMessage.error("Звіт не сформовано");
//     }
//   } catch (e) {
//     ElMessage("Помилка завантаження звіту...");
//   }
// };

onActivated(async () => {
  await getManeger();
  await getAllKasa();
});
</script>

<style scoped>
.el-table {
  padding: 10px;
  background: #4caf5045;
  border-radius: 25px;
}

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

@media (max-width: 1200px) {
  .el-select,
  .el-cascader,
  .el-date-picker,
  .el-button {
    margin: 2px 0 !important;
  }
  .el-table {
    font-size: 10pt;
  }
}

.expand-content {
  padding: 5px;
  background: #4caf5045;
  border-radius: 25px;
  margin: 0px;
}
</style>
