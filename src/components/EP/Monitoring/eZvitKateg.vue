<template>
  <div class="card" style="margin: 0 0 10px 0">
    <el-row wrap>
      <!-- Менеджер @change="getMonitoring"-->
      <el-col :xs="24" :sm="12" :md="12" :lg="6">
        <el-select
          v-model="checkManeger"
          multiple
          clearable
          collapse-tags
          placeholder="оберіть менеджера..."
          popper-class="custom-header"
          :max-collapse-tags="1"
          style="width: 100%"
          @change="getzvitMaterial"
        >
          <template #header>
            <el-checkbox v-model="checkAll" :indeterminate="indeterminate" @change="handleCheckAll">
              Усі
            </el-checkbox>
          </template>
          <el-option v-for="item in listManeger" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </el-col>

      <!-- Матеріал   -->
      <el-col :xs="24" :sm="12" :md="12" :lg="6">
        <el-cascader
          v-model="checkMaterial"
          :options="options"
          :props="propsCascader"
          clearable
          collapse-tags
          placeholder="оберіть матеріал..."
          :max-collapse-tags="2"
          style="margin-left: 10px"
          @change="getzvitMaterial"
        />
      </el-col>

      <!-- формування усіх/або тільки ВП або тільки КЛ -->
      <el-col :xs="12" :sm="12" :md="12" :lg="6">
        <el-radio-group style="margin-left: 2px" v-model="typeP">
          <el-radio-button label="усі" value="all" />
          <el-radio-button label="Пункти" value="sz" />
          <el-radio-button label="Склади" value="cm" />
        </el-radio-group>
      </el-col>

      <el-col :xs="12" :sm="12" :md="12" :lg="6">
        <el-radio-group v-model="punktLayout" @change="changeLayout()" style="margin-left: 10px">
          <el-radio-button value="false" label="+Пункт" />
          <el-radio-button value="true" label="-Пункт" />
        </el-radio-group>
      </el-col>
    </el-row>

    <el-row wrap>
      <!-- Період формування -->
      <el-col :xs="24" :sm="12" :md="12" :lg="12">
        <el-switch v-model="isPeriod" style="margin-left: 20px" />
        <el-date-picker
          v-model="valueDate"
          type="daterange"
          format="DD.MM.YYYY"
          :start-placeholder="getDate"
          :end-placeholder="getDate"
          :disabled="!isPeriod"
          :shortcuts="shortCuts"
          style="margin-left: 5px"
          @change="getzvitMaterial"
        />
      </el-col>

      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-button type="primary" plain :icon="Refresh" style="width: 100%" @click="getzvitMaterial">
          Оновити
        </el-button>
      </el-col>

      <!-- Розкрити/закрити підкатегорію по номенклатурі -->
      <el-col :xs="12" :sm="24" :md="6" :lg="6">
        <el-radio-group v-model="kategotiaLayout" @change="changeLayout()" style="margin-left: 10px">
          <el-radio-button value="false" label="+Категорія" />
          <el-radio-button value="true" label="-Категорія" />
        </el-radio-group>
      </el-col>
    </el-row>
  </div>

  <el-table :data="filterTable" :row-style="{ background: '#bad7da' }">
    <el-table-column type="index"></el-table-column>
    <!-- //менеджер -->
    <el-table-column>
      <template #default="scope">
        <el-icon><User /></el-icon>
        <span style="margin-left: 10px">{{ scope.row.pib }}</span>

        <!-- //пункты -->
        <el-table
          :data="scope.row.listPunkt"
          :show-header="true"
          :key="forceRenderUser"
          :default-expand-all="punktLayout == 'true'"
          stripe
          border
        >
          <el-table-column type="expand">
            <template #default="punkt">
              <div class="expand-contentBig">
                <!-- //категории -->
                <el-table
                  :data="punkt.row.listKateg"
                  stripe
                  border
                  :default-expand-all="kategotiaLayout == 'true'"
                >
                  <el-table-column type="expand">
                    <template #default="kateg">
                      <div class="expand-content">
                        <!-- материал -->
                        <el-table
                          :data="kateg.row.listMaterial"
                          :show-header="false"
                          style="margin-left: 4%; width: 96%; background: #124578"
                          stripe
                          border
                        >
                          <el-table-column>
                            <template #default="props">
                              <div>{{ props.row.name_M }}<BR /></div>
                            </template>
                          </el-table-column>
                          <!-- заготовка -->

                          <el-table-column prop="countZ" align="right">
                            <template #default="element">
                              <div>
                                {{
                                  element.row.countZ
                                    ? `${parseFloat(element.row.countZ).toLocaleString("ua")}  ${
                                        element.row.unit
                                      }`
                                    : "-"
                                }}
                              </div>
                            </template>
                          </el-table-column>
                          <el-table-column prop="countMoneyZ" align="left">
                            <template #default="element">
                              <div>
                                {{
                                  element.row.countMoneyZ
                                    ? `${parseFloat(element.row.countMoneyZ).toLocaleString("ua")} .грн`
                                    : "-"
                                }}
                              </div>
                            </template>
                          </el-table-column>

                          <!-- Відвантаження -->

                          <el-table-column prop="countV" align="right">
                            <template #default="element">
                              <div>
                                {{
                                  element.row.countV
                                    ? `${parseFloat(element.row.countV).toLocaleString("ua")} ${
                                        element.row.unit
                                      }`
                                    : "-"
                                }}
                              </div>
                            </template>
                          </el-table-column>
                          <el-table-column prop="countMoneyV" align="left">
                            <template #default="element">
                              <div>
                                {{
                                  element.row.countMoneyV
                                    ? `${parseFloat(element.row.countMoneyV).toLocaleString("ua")} .грн`
                                    : "-"
                                }}
                              </div>
                            </template>
                          </el-table-column>
                        </el-table>
                      </div>
                    </template>
                  </el-table-column>

                  <el-table-column prop="name_K" />

                  <el-table-column label="Надходження" align="center">
                    <el-table-column align="right">
                      <template #default="scope">
                        {{ parseFloat(scope.row.sumacountZ).toLocaleString("ua") }}
                        {{ scope.row.unit }}
                      </template>
                    </el-table-column>

                    <el-table-column align="left">
                      <template #default="scope">
                        {{ `${parseFloat(scope.row.sumacountMoneyZ).toLocaleString("ua")}.грн` }}
                      </template>
                    </el-table-column>
                  </el-table-column>

                  <el-table-column label="Відвантаження" align="center">
                    <el-table-column align="right">
                      <template #default="scope">
                        {{ parseFloat(scope.row.sumacountV).toLocaleString("ua") }}
                        {{ scope.row.unit }}
                      </template>
                    </el-table-column>
                    <el-table-column align="left">
                      <template #default="scope">
                        {{ `${parseFloat(scope.row.sumacountMoneyV).toLocaleString("ua")}.грн` }}
                      </template>
                    </el-table-column>
                  </el-table-column>
                </el-table>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="namePunkt"> </el-table-column>
        </el-table>
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
import { inject, ref, computed, onActivated, watch } from "vue";
import { useStore } from "vuex";
import { User, Refresh } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
const loading = ref(false);
const checkAll = ref(false);
const indeterminate = ref(false);
const checkManeger = ref([]);
const checkMaterial = ref([]);
const options = ref([]);
const propsCascader = { multiple: true, expandTrigger: "hover" };
const listManeger = ref([]);
const valueDate = ref([new Date(), new Date()]);
const isPeriod = ref(false);
const typeP = ref("all");
const kategotiaLayout = ref("false");
const punktLayout = ref("false");

const forceRenderUser = ref(0);

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

const changeLayout = () => {
  forceRenderUser.value++;
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
      ElMessage.success("Категорії оновлені");
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
  getzvitMaterial();
};

const formatDate = (valDate, mode = "ukr") => {
  const date = {
    d: valDate.getDate(),
    m: valDate.getMonth() + 1,
    y: valDate.getFullYear(),
  };
  return mode == "ukr"
    ? [(date.d < 10 ? "0" : "") + date.d, (date.m < 10 ? "0" : "") + date.m, date.y].join(".")
    : [date.y, (date.m < 10 ? "0" : "") + date.m, (date.d < 10 ? "0" : "") + date.d].join("-");
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabZvitKateg"].data;

  // const _tabl = {
  //   data: [
  //     {
  //       id_U: "1",
  //       pib: "Дзюба Сергей",
  //       listPunkt: [
  //         {
  //           id: "1",
  //           namePunkt: "Днепр_Береговая",
  //           listKateg: [
  //             {
  //               id_K: "2",
  //               name_K: "вторинна сировина",
  //               listMaterial: [
  //                 {
  //                   id_M: "31",
  //                   name_M: "Макулатура",
  //                   countZ: "1329.5",
  //                   countMoneyZ: "3988.5",
  //                   countV: "2135.5",
  //                   countMoneyV: "6406.5",
  //                 },
  //                 {
  //                   id_M: "32",
  //                   name_M: "Пет_бутылка",
  //                   countZ: "197",
  //                   countMoneyZ: "591",
  //                   countV: "0",
  //                   countMoneyV: "0",
  //                 },
  //               ],
  //               sumacountZ: "1526,5",
  //               sumacountMoneyZ: "4579,5",
  //               sumacountV: "2135,5",
  //               sumacountMoneyV: "6406.5",
  //             },
  //             {
  //               id_K: "3",
  //               name_K: "кольровий",
  //               listMaterial: [
  //                 {
  //                   id_M: "5",
  //                   name_M: "медь",
  //                   countZ: "20",
  //                   countMoneyZ: "8000",
  //                   countV: "10",
  //                   countMoneyV: "10000",
  //                 },
  //                 {
  //                   id_M: "56",
  //                   name_M: "алюмини",
  //                   countZ: "230",
  //                   countMoneyZ: "9500",
  //                   countV: "50",
  //                   countMoneyV: "2500",
  //                 },
  //               ],
  //               sumacountZ: "250",
  //               sumacountMoneyZ: "17500",
  //               sumacountV: "60",
  //               sumacountMoneyV: "12500",
  //             },
  //           ],
  //         },
  //       ],
  //     },
  //   ],
  // };
  return _tabl
    .map((user) => {
      return {
        ...user,
        listPunkt: user.listPunkt.filter(
          (punkt) =>
            (typeP.value == "sz" && punkt.typeP == "sz") ||
            typeP.value == "all" ||
            (typeP.value == "cm" && punkt.typeP == "cm")
        ),
      };
    })
    .filter((el) => el.listPunkt.length);
});

const getzvitMaterial = async () => {
  try {
    if (!checkManeger.value.length || isPeriod.value == false) {
      ElMessage.error("Оберіть менеджера та період віту");
      setting.value.tables["tabZvitKateg"].data = [];
      return;
    }
    loading.value = true;

    const response = await HTTP.post("", {
      _method: "getzvitMaterial",
      _id_U: getCurUser.value.id,
      _checkManeger: checkManeger.value,
      _checkMaterial: checkMaterial.value,
      _date_l: formatDate(valueDate.value[0], "eng"),
      _date_r: formatDate(valueDate.value[1], "eng"),
      _isPeriod: isPeriod.value ? 1 : 0,
    });

    setting.value.tables["tabZvitKateg"].data = response.data.ar_data;
    loading.value = false;
  } catch (e) {
    ElMessage.error("Помилка завантаження ");
  }
};

onActivated(async () => {
  await getManeger();
  await getKategories();
  await getzvitMaterial();
});
</script>

<style scoped>
.el-row {
  margin-bottom: 10px;
}
.expand-contentBig {
  padding: 10px;
  background: #4caf5045;
  border-radius: 25px;
  margin: 0px 5px;
}
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
    padding: 10px;
  }
  .card.active {
    background-color: var(--el-color-primary-light-9);
  }
}
</style>
