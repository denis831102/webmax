<template>
  <el-space :size="10" style="margin: 0 0 10px 0">
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

        <el-cascader
          v-model="checkMaterial"
          :options="options"
          :props="propsCascader"
          clearable
          collapse-tags
          placeholder="оберіть матеріал..."
          :max-collapse-tags="2"
          style="width: 240px"
        />

        <el-button
          type="primary"
          plain
          :icon="Refresh"
          @click="getMonitoring()"
        >
          Оновити
        </el-button>
      </el-space>
    </el-card>
  </el-space>

  <el-card v-if="false">
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

  <el-table
    :data="filterTable"
    row-style="background:#f4f4f5"
    v-loading="loading"
  >
    <el-table-column type="index" v-if="setting.displaySize == 'large'" />

    <el-table-column width="200" v-if="setting.displaySize == 'large'">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.pib }}</span>
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
                show-summary
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
                  label="частина"
                  width="150"
                  prop="percent"
                  sortable
                >
                  <template #default="props">
                    <el-progress
                      type="dashboard"
                      :percentage="props.row.percent"
                      :color="colors"
                      :stroke-width="15"
                    />
                    <div
                      v-if="setting.displaySize == 'small'"
                      style="padding: 5px 0 5px 10px; background: #c6e2ff69"
                    >
                      {{ parseFloat(props.row.count).toLocaleString("ru") }}
                      {{ props.row.unit }}
                    </div>
                  </template>
                </el-table-column>

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
import { inject, ref, computed, onActivated, watch } from "vue";
import { useStore } from "vuex";
import { User } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
const colors = [
  { color: "#ff55ff", percentage: 10 },
  { color: "#f56c6c", percentage: 20 },
  { color: "#e6a23c", percentage: 40 },
  { color: "#5cb87a", percentage: 60 },
  { color: "#1989fa", percentage: 80 },
  { color: "#6f7ad3", percentage: 100 },
];
const checkAll = ref(false);
const indeterminate = ref(false);
const checkManeger = ref([]);
const checkMaterial = ref([]);
const listManeger = ref([]);
const options = ref([]);
const loading = ref(true);
const propsCascader = { multiple: true, expandTrigger: "hover" };

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
