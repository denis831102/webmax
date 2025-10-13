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
      <!-- :class="{ card: setting.displaySize == 'large' }" -->

      <div class="card" style="margin: 0 0 10px 0">
        <el-row :gutter="20" wrap>
          <!-- свич та пошук матеріалів -->
          <el-col :xs="4" :sm="12" :md="12" :lg="7">
            <el-switch v-model="isFilter" @change="getBits" />

            <el-input
              v-model="search"
              size="normall"
              style="min-width: 100px; width: 80%; margin-left: 15px"
              placeholder="Пошук матеріала"
              :prefix-icon="Search"
              :disabled="!isFilter"
              v-if="setting.displaySize == 'large'"
            />
          </el-col>
          <!-- Дата -->
          <el-col :xs="20" :sm="12" :md="12" :lg="3">
            <el-date-picker
              v-model="curDate"
              type="date"
              format="DD.MM.YYYY"
              :start-placeholder="getDate"
              :end-placeholder="getDate"
              :disabled="!isFilter"
              @change="getBits"
              style="width: 100%"
              size="normal"
            />
          </el-col>
          <!-- Операціі -->
          <el-col :xs="24" :sm="12" :md="12" :lg="3">
            <el-button
              type="primary"
              :icon="HomeFilled"
              @click="getOperation"
              style="width: 100%"
            >
              <el-icon><Connection /></el-icon>
              <span>Операції</span>
            </el-button>
          </el-col>

          <!-- Оновити -->
          <el-col :xs="24" :sm="12" :md="12" :lg="3">
            <el-button
              type="primary"
              plain
              :icon="Refresh"
              style="width: 100%"
              @click="getBits()"
            >
              Оновити
            </el-button>
          </el-col>

          <!-- Виводити/невиводити "0" номенклатури -->
          <el-col
            :xs="24"
            :sm="12"
            :md="5"
            :lg="8"
            style="width: auto"
            v-if="setting.displaySize == 'large'"
          >
            <el-radio-group style="width: 100%" v-model="withoutZeroMat">
              <el-radio-button value="allMat" label="Всі" />
              <el-radio-button value="withoutZero" label="Наявні" />
            </el-radio-group>
          </el-col>
        </el-row>
      </div>

      <el-row :gutter="20">
        <el-col :span="24">
          <el-table
            :data="filterTable"
            :class="{ marginTabl: setting.displaySize == 'large' }"
            :default-expand-all="+viewMaterial"
            v-loading="loading"
            stripe
          >
            <el-table-column type="expand">
              <template #default="props">
                <div class="expand-content">
                  <el-tag
                    type="success"
                    style="
                      margin-top: -25px;
                      margin-left: 50%;
                      transform: translateX(-50%);
                      border: 1px solid #66bb6a;
                    "
                  >
                    <el-icon><PieChart /></el-icon> Залишки по категоріїї:
                    {{ props.row.name_K }}
                  </el-tag>

                  <el-table
                    :data="props.row.listMater"
                    border="true"
                    style="width: 100%"
                  >
                    <el-table-column
                      type="index"
                      width="60"
                      v-if="setting.displaySize == 'large'"
                    />

                    <el-table-column label="номенклатура" prop="name_M" />

                    <el-table-column
                      label="кількість"
                      :width="setting.displayWidth * 0.22"
                      prop="count"
                    >
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

            <el-table-column
              type="index"
              v-if="setting.displaySize == 'large'"
            />

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
import { inject, ref, computed, onActivated, onUpdated, watch } from "vue";
import { useStore } from "vuex";
import { Search, Calendar, Connection, Refresh } from "@element-plus/icons-vue";
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
const isFilter = ref(false);
const curDate = ref(new Date());
const loading = ref(true);
const viewMaterial = ref(false);
const withoutZeroMat = ref("withoutZero");

watch(
  () => [
    setting.value.dialog["editManeger"].chooseUser,
    setting.value.dialog["editManeger"].idManeger,
  ],
  () => getPunktCur()
);

watch(
  () => [isFilter.value, search.value],
  () => {
    viewMaterial.value = +isFilter.value && search.value.length;
  }
);

const filterTable = computed(() => {
  const _tabl = [...setting.value.tables["tabBits"].data];
  return _tabl
    .map((kateg) => {
      return {
        ...kateg,
        listMater: kateg.listMater.filter(
          (mater) =>
            ((withoutZeroMat.value == "withoutZero" && mater.count != 0) ||
              withoutZeroMat.value == "allMat") &&
            ((isFilter.value &&
              mater.name_M
                .toLowerCase()
                .includes(search.value.toLowerCase())) ||
              !isFilter.value)
        ),
      };
    })
    .filter((kateg) => kateg.listMater.length);
  // return !search.value.length || !isFilter.value
  //   ? setting.value.tables["tabBits"].data
  //   : JSON.parse(JSON.stringify(setting.value.tables["tabBits"].data)).filter(
  //       (row) => {
  //         row.listMater = row.listMater.filter((mater) =>
  //           mater.name_M.toLowerCase().includes(search.value.toLowerCase())
  //         );
  //         return row.listMater.find((mater) =>
  //           mater.name_M.toLowerCase().includes(search.value.toLowerCase())
  //         );
  //       }
  //     );
});

const activeIdPunkt = computed(() => {
  const punkt = punkts.value.find((el) => el.name == activeName.value);
  return punkts.value.length && punkt ? punkt.id : 0;
});

// const classMargin = computed(() => ({
//   marginTabl: setting.value.displaySize == "large",
// }));

const getPunktCur = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getPunktCur",
        _id_U:
          setting.value.dialog["editManeger"].chooseUser == "user"
            ? getCurUser.value.id
            : setting.value.dialog["editManeger"].idManeger,
      },
    });

    punkts.value = response.data;

    const nameUser =
      setting.value.dialog["editManeger"].chooseUser == "user"
        ? getCurUser.value.PIB.toUpperCase()
        : setting.value.tables["tabUser"].data
            .find(
              (el) => el.id == setting.value.dialog["editManeger"].idManeger
            )
            .PIB.toUpperCase();

    setting.value.titleTable = `${setting.value.tables["tabBits"].title} ${nameUser}`;
    // ElMessage.success("Пункти поточного користувача оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження пунктів");
  }
};

const getBits = async () => {
  try {
    loading.value = true;
    const response = await HTTP.post("", {
      _method: "getBits",
      _id_U: getCurUser.value.id,
      _id_P: activeIdPunkt.value,
      _date: isFilter.value ? formatDate(curDate.value, "eng") : "",
    });

    setting.value.tables["tabBits"].data = response.data;
    loading.value = false;

    if (+getSettingUser.value.isShowMes) {
      let limitDate = isFilter.value
        ? formatDate(curDate.value, "ukr")
        : formatDate(new Date(), "ukr");
      ElMessage.success(
        `Залишки оновлені для "${activeName.value}" до ${limitDate}`
      );
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

<style scoped>
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
.marginTabl {
  left: 50%;
  transform: translateX(-50%);
  width: 50%;
}

/* @media (max-width: 1200px) {
  .card {
    /* border: 1px   solid var(--el-border-color); 
    margin-left: 5px;
    padding: 20px;
  }
  .card.active {
    background-color: var(--el-color-primary-light-9);
  }
} */

@media (min-width: 768px) and (max-width: 1200px) {
  .card {
    margin-left: 5px;
    padding: 5px;
  }
  .card.active {
    background-color: var(--el-color-primary-light-9);
  }
}

@media (max-width: 576px) {
  .card {
    /* border: 1px solid var(--el-border-color); */
    margin-left: 5px;
    padding: 5px;
  }
  .card.active {
    background-color: var(--el-color-primary-light-9);
  }
}
</style>
