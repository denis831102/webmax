<template>
  <eDialog_Operation
    v-model:visible="setting.dialog['editBits'].visible"
    :namePunkt="activeName"
    :idPunkt="activeIdPunkt"
  />

  <el-tabs
    v-model="activeName"
    @tab-change="changeTab"
    type="border-card"
    class="demo-tabs"
  >
    <el-tab-pane v-for="punkt in punkts" :key="punkt.id" :name="punkt.name">
      <template #label>
        <span class="custom-tabs-label">
          <el-icon><calendar /></el-icon>
          <span>{{ punkt.name }}</span>
        </span>
      </template>

      <el-row :gutter="20" style="margin: 0 0 20px 10px">
        <el-col :span="5">
          <el-input
            v-model="search"
            size="small"
            style="width: 100%; height: 100%"
            placeholder="Пошук матеріала"
            :prefix-icon="Search"
          />
        </el-col>
        <el-col :span="13">
          <el-button-group>
            <el-button type="primary" :icon="HomeFilled" @click="getOperation">
              <el-icon><Connection /></el-icon>
              <span style="margin-left: 5px">Поточні операції</span>
            </el-button>
            <el-button type="primary" plain :icon="Refresh" @click="getBits()">
              Оновити
            </el-button>
          </el-button-group>
        </el-col>
      </el-row>

      <el-row :gutter="20" style="margin: 0 20% 20px 20%">
        <el-col :span="24">
          <el-table :data="filterTable">
            <el-table-column type="expand">
              <template #default="props">
                <div style="padding: 20px; background: #c6e2ff69">
                  <h3 style="margin: 0px 0 10px 0">
                    <el-icon><PieChart /></el-icon>
                    <span style="margin-left: 5px">Залишки по категоріїї</span>
                    <el-check-tag type="primary">
                      {{ props.row.name_K }}
                    </el-check-tag>
                  </h3>
                  <el-table
                    :data="props.row.listMater"
                    border="true"
                    style="margin-left: 2%; width: 98%"
                    show-summary
                  >
                    <el-table-column type="index" width="60" />

                    <el-table-column label="номенклатура" prop="name_M" />

                    <el-table-column label="кількість" width="150" prop="count">
                      <template #default="props">
                        <div
                          style="padding: 5px 0 5px 10px; background: #c6e2ff69"
                        >
                          {{ parseFloat(props.row.count).toLocaleString("ru") }}
                          {{ props.row.unit }}
                        </div>
                      </template>
                    </el-table-column>
                  </el-table>
                </div>
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
        </el-col>
      </el-row>
    </el-tab-pane>
  </el-tabs>
</template>

<script setup>
import { inject, ref, computed, onActivated, onUpdated } from "vue";
import { useStore } from "vuex";
import { Search, Calendar, Connection } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
const changeSettingUser = (obj) => store.commit("changeSettingUser", obj);
const search = ref("");
const punkts = ref([]);
const activeName = ref("");

const filterTable = computed(() => {
  // const _tabl = [...setting.value.tables["tabBits"].data];
  return !search.value.length
    ? setting.value.tables["tabBits"].data
    : JSON.parse(JSON.stringify(setting.value.tables["tabBits"].data)).filter(
        (row) => {
          row.listMater = row.listMater.filter((mater) =>
            mater.name_M.toLowerCase().includes(search.value.toLowerCase())
          );
          return row.listMater.find((mater) =>
            mater.name_M.toLowerCase().includes(search.value.toLowerCase())
          );
        }
      );
});

const activeIdPunkt = computed(() => {
  const punkt = punkts.value.find((el) => el.name == activeName.value);
  return punkts.value.length && punkt ? punkt.id : 0;
});

const getPunktCur = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getPunktCur",
        _id_U: getCurUser.value.id,
      },
    });

    punkts.value = response.data;
    // ElMessage.success("Пункти поточного користувача оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження пунктів");
  }
};

const getBits = async () => {
  try {
    const response = await HTTP.post("", {
      _method: "getBits",
      _id_U: getCurUser.value.id,
      _id_P: activeIdPunkt.value,
    });

    setting.value.tables["tabBits"].data = response.data;

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Залишки оновлені");
    }
  } catch (e) {
    ElMessage("Помилка завантаження залишків на складі");
  }
};

const changeTab = (tab) => {
  changeSettingUser({ nameTab: tab });
};

const getOperation = () => {
  setting.value.comps.curComp = "eOperation";
  setting.value.titleTable = setting.value.tables["tabTransaction"].title;
};

onActivated(async () => {
  await getPunktCur();
  await getBits();

  activeName.value = getSettingUser.value.nameTab.length
    ? getSettingUser.value.nameTab
    : punkts.value[0]["name"];
});

onUpdated(async () => {
  await getBits();
});
</script>

<style>
.demo-tabs > .el-tabs__content {
  padding: 32px;
  color: #6b778c;
  font-size: 32px;
  font-weight: 600;
}
.demo-tabs .custom-tabs-label .el-icon {
  vertical-align: middle;
}
.demo-tabs .custom-tabs-label span {
  vertical-align: middle;
  margin-left: 4px;
}
</style>
