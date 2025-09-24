<template>
  <el-space :size="10" style="margin: 0 0 10px 0">
    <el-row :gutter="10" wrap>
      <!-- Матеріал -->
      <el-col :span="3">
        <el-cascader
          v-model="checkMaterial"
          :options="options"
          :props="propsCascader"
          clearable
          collapse-tags
          placeholder="оберіть матеріал..."
          :max-collapse-tags="2"
          style="width: 240px"
          @change="getMonitoring"
        />
      </el-col>

      <!-- Фільтр (перемикач) -->
      <el-col :span="3">
        <el-switch v-model="isFilter" @change="getMonitoring" />
      </el-col>

      <!-- Дата -->
      <el-col :span="3">
        <el-date-picker
          v-model="curDate"
          type="date"
          format="DD.MM.YYYY"
          :start-placeholder="getDate"
          :end-placeholder="getDate"
          :disabled="!isFilter"
          @change="getMonitoring"
          size="normal"
          style="margin-left: -10px"
        />
      </el-col>

      <!-- Оновити -->
      <el-col :span="3">
        <el-button
          type="primary"
          plain
          :icon="Refresh"
          @click="getMonitoring()"
        >
          Оновити
        </el-button>
      </el-col>
    </el-row>
  </el-space>

  <el-table
    :data="filterTable"
    row-style="background:#f4f4f5"
    v-loading="loading"
  >
    <el-table-column>
      <template #default="scope">
        <div
          v-if="setting.displaySize == 'small'"
          style="display: flex; align-items: center; margin: 0 0 10px 15px"
        >
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.pib }}</span>
        </div>

        <el-table :data="scope.row.listKateg">
          <el-table-column type="expand">
            <template #default="props">
              <h3 style="margin: 0px 0 10px 10px">
                <el-icon><PieChart /></el-icon>
                <span style="margin-left: 5px">Залишки по категоріїї</span>
                <el-check-tag type="primary" style="margin-left: 10px">
                  {{ props.row.name_K }}
                </el-check-tag>
              </h3>
              <el-table
                :data="props.row.listMaterial"
                border="true"
                style="margin-left: 2%; width: 98%"
              >
                <el-table-column
                  type="index"
                  width="60"
                  v-if="setting.displaySize == 'large'"
                />

                <el-table-column
                  label="номенклатура"
                  prop="name_M"
                  width="150"
                />

                <el-table-column
                  label="кількість"
                  width="150"
                  prop="count"
                  sortable
                  v-if="setting.displaySize == 'large'"
                >
                  <template #default="props">
                    <div style="padding: 5px 0 5px 10px; background: #c6e2ff69">
                      {{ parseFloat(props.row.count).toLocaleString("ru") }}
                      {{ props.row.unit }}
                    </div>
                  </template>
                </el-table-column>

                <el-table-column
                  label="детальніше"
                  :width="setting.displaySize == 'small' ? 300 : 0"
                >
                  <template #default="props">
                    <el-table :data="props.row.listPunkt">
                      <el-table-column label="пункт" prop="name_P" sortable />

                      <el-table-column label="кількість" prop="count" sortable>
                        <template #default="props">
                          <div
                            style="
                              padding: 5px 0 5px 10px;
                              background: #c6e2ff69;
                            "
                          >
                            {{
                              parseFloat(props.row.count).toLocaleString("ru")
                            }}
                            {{ props.row.unit }}
                          </div>
                        </template>
                      </el-table-column>

                      <el-table-column label="відсоток" prop="percent">
                        <template #default="props">
                          <el-progress
                            :text-inside="true"
                            :stroke-width="26"
                            :percentage="props.row.percent"
                          />
                        </template>
                      </el-table-column>
                    </el-table>
                  </template>
                </el-table-column>
              </el-table>
            </template>
          </el-table-column>

          <el-table-column type="index" />

          <el-table-column label="Категорія" prop="name_K"> </el-table-column>

          <el-table-column label="Загальна кількість">
            <template #default="props">
              <div style="padding: 5px 0 5px 10px; background: #c6e2ff69">
                {{ parseFloat(props.row.summa_K).toLocaleString("ua") }}
                {{ props.row.unit }}
              </div>
            </template>
          </el-table-column>
        </el-table>
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
import { inject, ref, computed, onActivated } from "vue";
import { useStore } from "vuex";
import { Refresh } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
const checkMaterial = ref([]);
const options = ref([]);
const loading = ref(true);
const propsCascader = { multiple: true, expandTrigger: "hover" };
const isFilter = ref(false);
const curDate = ref(new Date());

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabAnalitikaM"].data;
  return _tabl
    .map((user) => {
      return {
        ...user,
        listKateg: user.listKateg
          .map((kateg) => {
            return {
              ...kateg,
              listMaterial: kateg.listMaterial.filter(
                (mater) => mater.count != 0
              ),
            };
          })
          .filter((el) => el.listMaterial.length),
      };
    })
    .filter((el) => el.listKateg.length);
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

const getMonitoring = async () => {
  loading.value = true;
  try {
    const response = await HTTP.post("", {
      _method: "getMonitoring",
      _id_U: getCurUser.value.id,
      _checkManeger: [getCurUser.value.id],
      _checkMaterial: checkMaterial.value,
      _date: isFilter.value ? formatDate(curDate.value, "eng") : "",
    });

    setting.value.tables["tabAnalitikaM"].data = response.data.ar_data;
    loading.value = false;

    if (+getSettingUser.value.isShowMes) {
      let limitDate = isFilter.value
        ? formatDate(curDate.value, "ukr")
        : formatDate(new Date(), "ukr");
      ElMessage.success(`Аналітика по номенклатурі на ${limitDate} оновлена`);
    }
  } catch (e) {
    ElMessage.error("Помилка завантаження аналітики");
  }
};

const getKategories = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getKategories",
      },
    });

    options.value = response.data
      .map((el) => {
        const newChildren = el.listMaterial.map((mat) => {
          return { value: mat.id_M, label: mat.name_M };
        });
        return { value: el.id_K, label: el.name_K, children: newChildren };
      })
      .filter((el) => {
        return el.value != 1 && el.value != 6;
      });

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Категоріїї оновлені");
    }
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

// const loadReport = async () => {
//   try {
//     const response = await HTTP.post("", {
//       _method: "loadReport",
//       _id_U: getCurUser.value.id,
//       _nameReport: "analitikaResurs",
//       _checkManeger: checkManeger.value,
//       _checkMaterial: checkMaterial.value,
//       _date: isFilter.value ? formatDate(curDate.value, "eng") : "",
//     });

//     if (response.data.isSuccesfull) {
//       loadFile(
//         response.data.fileName,
//         response.data.content,
//         response.data.mime
//       );

//       ElMessage.success("Звіт сформовано");
//     } else {
//       ElMessage.error("Звіт не сформовано");
//     }
//   } catch (e) {
//     ElMessage("Помилка завантаження звіту...");
//   }
// };

onActivated(async () => {
  await getKategories();
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
