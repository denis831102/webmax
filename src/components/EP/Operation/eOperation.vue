<template>
  <eDialog_Operation
    v-model:visible="setting.dialog['editOperation'].visible"
    :namePunkt="activeName"
    :idPunkt="activeIdPunkt"
  />
  <eDialog_Sort
    v-model:visible="setting.dialog['createPeresort'].visible"
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

      <el-space :size="10" style="margin: -5px 0 10px 0">
        <el-card v-if="setting.displaySize == 'large'">
          <el-space :size="10" style="padding: 10px 5px">
            <div class="statistic-card">
              <el-statistic
                :value="kassa.summa"
                title=""
                precision="2"
                group-separator=" "
              >
                <template #title>
                  <div
                    style="
                      display: inline-flex;
                      align-items: center;
                      font-size: 20px;
                      font-style: oblique;
                    "
                  >
                    Загальна каса, грн.
                  </div>
                </template>
              </el-statistic>

              <div class="statistic-footer">
                <div class="footer-item">
                  <span>з минулим днем </span>
                  <span
                    :class="[kassa.percent > 0 ? 'green' : 'red']"
                    :title="kassa.oldSumma"
                  >
                    {{ kassa.percent }}%
                    <el-icon>
                      <CaretTop v-if="kassa.percent > 0" />
                      <CaretBottom v-else />
                    </el-icon>
                  </span>
                </div>
              </div>
            </div>
          </el-space>
          <el-button
            :size="20"
            style="width: 90px; height: 90px; margin: 10px 10px 10px 10px"
            type="primary"
            plain
            @click="getBits"
          >
            <el-icon
              style="position: relative; left: 24pt; top: -8px; font-size: 30pt"
              ><ShoppingCartFull
            /></el-icon>
            <div
              style="
                position: relative;
                left: -20px;
                top: 24px;
                font-size: 13pt;
              "
            >
              Залишки
            </div>
          </el-button>
        </el-card>

        <el-card v-if="setting.displaySize == 'large'" style="padding: 0px 5px">
          <el-row :gutter="10">
            <el-col :span="3">
              <el-switch v-model="isPeriod" @change="getTransaction" />
            </el-col>
            <el-col :span="2"> </el-col>

            <el-col :span="8">
              <el-date-picker
                v-model="valueDate"
                type="daterange"
                format="DD.MM.YYYY"
                :start-placeholder="getDate"
                :end-placeholder="getDate"
                :disabled="!isPeriod"
                :shortcuts="shortCuts"
                @change="getTransaction"
                style="width: 210px; padding: 20px 10px; margin-left: -10px"
              />
            </el-col>
          </el-row>
          <el-row :gutter="10">
            <el-col :span="4"> </el-col>
            <el-col :span="11">
              <el-input
                v-model="search"
                size="large"
                style="width: 210px"
                placeholder="Пошук за коментарем"
                @input="debouncedChange"
                :prefix-icon="Search"
              />
            </el-col>
          </el-row>
        </el-card>

        <el-button-group>
          <el-button
            v-if="setting.displaySize == 'small'"
            style="width: 110px"
            plain
            @click="getBits"
          >
            <el-icon><Money /></el-icon>
            <span>{{ kassa.summa }}</span>
          </el-button>

          <el-button
            v-if="setting.displaySize == 'small'"
            style="width: 110px; font-style: 5pt; background: #90939959"
            plain
            @click="getBits"
          >
            <el-icon><ShoppingCartFull /></el-icon>
            <span style="margin-left: 5px">Залишки</span>
          </el-button>

          <el-button
            v-if="setting.displaySize == 'small'"
            type="warning"
            :icon="HomeFilled"
            style="width: 110px"
            @click="loadFiltr"
          >
            <el-icon><Filter /></el-icon>
            <span style="margin-left: 5px">Фільтр</span>
          </el-button>

          <el-button
            type="primary"
            :icon="DocumentAdd"
            @click="newTransaction()"
            >Нова операція
          </el-button>

          <el-button
            type="info"
            :icon="Sort"
            style="width: 110px"
            @click="createPeresort()"
          >
            Пересорт
          </el-button>

          <el-button
            type="primary"
            plain
            :icon="Refresh"
            style="width: 110px"
            @click="getTransaction()"
          >
            Оновити
          </el-button>
        </el-button-group>
      </el-space>

      <!-- <el-scrollbar height="600px"> -->
      <el-table :data="filterTable" v-loading="loading" stripe>
        <el-table-column type="expand">
          <template #default="props">
            <div style="padding: 20px; background: #c6e2ff69">
              <h3 style="margin: 0px 0 10px 0">
                Транзакція за операцією
                <el-check-tag type="primary">
                  {{ props.row.comment }}
                </el-check-tag>
                <el-check-tag type="success" style="margin-left: 5px">
                  {{ props.row.date }} - {{ props.row.time }}
                </el-check-tag>
              </h3>

              <el-table
                :data="props.row.listOper"
                border="true"
                style="background: #c6e2ff"
                show-summary
              >
                <el-table-column label="вид операції" prop="name_V" />
                <el-table-column label="номенклатура" prop="name_M" />
                <el-table-column label="кількість" prop="count">
                  <template #default="props">
                    {{ parseFloat(props.row.count).toLocaleString("ua") }}
                    {{ props.row.unit != "грн" ? props.row.unit : "" }}
                  </template>
                </el-table-column>

                <el-table-column label="ціна одиниці">
                  <template #default="props">
                    {{ parseFloat(props.row.price).toLocaleString("ua") }}
                    {{
                      props.row.unit == "грн"
                        ? props.row.unit
                        : `грн/${props.row.unit}`
                    }}
                  </template>
                </el-table-column>

                <el-table-column label="сума" prop="suma">
                  <template #default="props">
                    {{ parseFloat(props.row.suma).toLocaleString("ua") }} грн.
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </template>
        </el-table-column>

        <el-table-column type="index" v-if="setting.displaySize == 'large'" />

        <el-table-column prop="date">
          <template #header>
            <el-icon><Calendar /></el-icon>
            <span style="margin-left: 10px">Дата</span>
          </template>
          <template #default="scope">
            <div style="display: flex; align-items: center">
              <span style="margin-left: 10px"
                >{{ scope.row.date }} - {{ scope.row.time }}</span
              >
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Коментар" prop="comment"> </el-table-column>

        <el-table-column label="Сума" prop="suma" width="150">
          <template #default="scope">
            <div style="display: flex; align-items: center">
              <span style="margin-left: 10px"
                >{{
                  parseFloat(scope.row.suma).toLocaleString("ua")
                }}
                грн.</span
              >
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Дії">
          <template #default="scope">
            <el-button-group class="ml-4">
              <el-button
                size="small"
                @click="editTransaction(scope.$index, scope.row)"
                title="Редагування транзакції"
                :disabled="scope.row.isEdit == 0"
              >
                <el-icon><Edit /></el-icon>
              </el-button>

              <el-button
                size="small"
                type="danger"
                @click="delTransaction(scope.$index, scope.row)"
                title="Видалення транзакції"
                :disabled="scope.row.isDel == 0"
              >
                <el-icon><DeleteFilled /></el-icon>
              </el-button>
              <el-button
                size="small"
                type="success"
                @click="copyTransaction(scope.$index, scope.row)"
                title="Створення транзакції за зразком"
              >
                <el-icon><CopyDocument /></el-icon>
              </el-button>
            </el-button-group>

            <el-switch
              v-if="+getCurUser.listAccess[5]"
              inline-prompt
              active-text="edit"
              inactive-text="edit"
              :modelValue="scope.row.isEdit == 1"
              @change="changeCheck(scope.row, 'Edit')"
              style="margin-left: 10px"
            />
            <el-switch
              v-if="+getCurUser.listAccess[6]"
              inline-prompt
              active-text="del"
              inactive-text="del"
              :modelValue="scope.row.isDel == 1"
              @change="changeCheck(scope.row, 'Del')"
              style="margin-left: 10px"
            />
          </template>
        </el-table-column>
      </el-table>
      <!-- </el-scrollbar> -->

      <el-pagination
        background
        layout="prev, pager, next"
        :total="setPagination.total"
        v-model:page-size="setPagination.sizePage"
        v-model:current-page="setPagination.currentPage"
        v-if="setPagination.total > 0"
        style="margin-top: 25px; float: right"
        @current-change="getTransaction"
      />
    </el-tab-pane>
  </el-tabs>
</template>

<script setup>
import {
  inject,
  ref,
  computed,
  watch,
  onActivated,
  onUpdated,
  reactive,
  onUnmounted,
  h,
} from "vue";
import { useStore } from "vuex";
import {
  Search,
  Calendar,
  CaretTop,
  ShoppingCartFull,
  Refresh,
  Sort,
  DocumentAdd,
} from "@element-plus/icons-vue";
import {
  ElMessage,
  ElMessageBox,
  ElNotification,
  ElSwitch,
  ElDatePicker,
  ElInput,
} from "element-plus";
import eDialog_Operation from "@/components/EP/Operation/eDialog_Operation";
import eDialog_Sort from "@/components/EP/Operation/eDialog_Sort";
import { HTTP } from "@/hooks/http";
import * as _ from "lodash";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
const changeSettingUser = (obj) => store.commit("changeSettingUser", obj);
const search = ref("");
const valueDate = ref([new Date(), new Date()]);
const punkts = ref([]);
const activeName = ref("");
const isPeriod = ref(false);
const curDate = ref(new Date());
const setPagination = reactive({
  currentPage: 1,
  sizePage: 5,
  total: 1,
});
const debouncedChange = ref();
const loading = ref(true);
const kassa = reactive({
  summa: 0,
  oldSumma: 0,
  percent: 0,
});

watch(
  () => [activeName.value, setting.value.dialog["editOperation"].visible],
  () => getTransaction()
);

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

const getTransaction = async () => {
  if (
    setting.value.dialog["editOperation"].visible ||
    setting.value.dialog["createPeresort"].visible
  )
    return;

  try {
    loading.value = true;
    const response = await HTTP.get("", {
      params: {
        _method: "getTransaction",
        _id_U: getCurUser.value.id,
        _id_P: activeIdPunkt.value,
        _currentPage: setPagination.currentPage,
        _sizePage: setPagination.sizePage,
        _date_l: formatDate(valueDate.value[0], "eng"),
        _date_r: formatDate(valueDate.value[1], "eng"),
        _isPeriod: isPeriod.value ? 1 : 0,
        _search: search.value.toLowerCase(),
      },
    });

    setting.value.tables["tabTransaction"].data = response.data.ar_data;
    setPagination.total = response.data.total;
    kassa.summa = response.data.kassa;
    kassa.oldSumma = response.data.oldkassa;
    kassa.percent = response.data.percent;
    loading.value = false;

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success(
        response.data.total > 0
          ? `Транзакції ${getCurUser.value.PIB.toUpperCase()} для ${
              activeName.value
            } оновлені`
          : `Транзакції ${getCurUser.value.PIB.toUpperCase()} для ${
              activeName.value
            } відсутні`
      );
    }
  } catch (e) {
    ElMessage.error("Помилка завантаження транзакцій");
  }
};

const newTransaction = () => {
  setting.value.dialog["editOperation"].initiator = "createOperation";
  setting.value.dialog["editOperation"].visible = true;
};

const editTransaction = (ind, row) => {
  setting.value.tables["tabTransaction"].curRow = row;
  setting.value.dialog["editOperation"].initiator = "editOperation";
  setting.value.dialog["editOperation"].visible = true;
};

const delTransaction = async (ind, row) => {
  ElMessageBox.confirm(
    `Ви точно бажаєте видалити транзакцію '${row.comment.toUpperCase()}' на суму ${
      row.suma
    } грн. за ${row.date} - ${row.time}?`
  )
    .then(() => {
      delTransaction_Ok(row);
    })
    .catch(() => {});
};

const delTransaction_Ok = async (row) => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "delTransaction",
        _id_T: row.id_T,
      },
    });

    if (+row.id_T_child !== 0) {
      await HTTP.get("", {
        params: {
          _method: "delTransaction",
          _id_T: row.id_T_child,
        },
      });
    }

    if (response.data.isSuccesfull) {
      const _tab = setting.value.tables["tabTransaction"];
      _tab.data = _tab.data.filter((el) => el.id_T !== row.id_T);

      kassa.summa = response.data.kassa;
      kassa.oldSumma = response.data.oldkassa;
      kassa.percent = response.data.percent;
    } else {
      ElMessage.error("Транзакцію не видалено");
    }
  } catch (e) {
    ElMessage("Помилка видалення транзакції");
  }
};

const copyTransaction = (ind, row) => {
  setting.value.tables["tabTransaction"].curRow = row;
  setting.value.dialog["editOperation"].initiator = "copyOperation";
  setting.value.dialog["editOperation"].visible = true;
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabTransaction"];
  // const arDateL = formatDate(valueDate.value[0]).split(".");
  // const arDateR = formatDate(valueDate.value[1]).split(".");
  // const leftDate = new Date(`${arDateL[2]}-${arDateL[1]}-${arDateL[0]}`);
  // const rightDate = new Date(`${arDateR[2]}-${arDateR[1]}-${arDateR[0]}`);

  // return _tabl.data.filter((row) => {
  //   const arDate = row.date.split(".");
  //   const tDate = new Date(`${arDate[2]}-${arDate[1]}-${arDate[0]}`);

  //   return isDate.value
  //     ? row.comment.toLowerCase().includes(search.value.toLowerCase()) &&
  //         tDate >= leftDate &&
  //         tDate <= rightDate
  //     : row.comment.toLowerCase().includes(search.value.toLowerCase());
  // });

  return _tabl.data;
});

const activeIdPunkt = computed(() => {
  const punkt = punkts.value.find((el) => el.name == activeName.value);
  return punkts.value.length && punkt ? punkt.id : 0;
});

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

const loadFiltr = () => {
  ElNotification({
    title: "Фільтр",
    message: () => [
      h(ElSwitch, {
        modelValue: isPeriod.value,
        "onUpdate:modelValue": (val) => {
          isPeriod.value = val;
        },
        onChange: () => {
          getTransaction();
        },
      }),
      h(ElDatePicker, {
        modelValue: valueDate.value,
        "onUpdate:modelValue": (val) => {
          valueDate.value = val;
        },
        type: "daterange",
        format: "DD.MM.YYYY",
        "start-placeholder": getDate.value,
        "end-placeholder": getDate.value,
        disabled: !isPeriod.value,
        shortcuts: shortCuts,
        style: "width: 210px; padding: 20px 10px; margin-left: 15px",
        onChange: () => {
          getTransaction();
        },
      }),
      h(ElInput, {
        modelValue: search.value,
        "onUpdate:modelValue": (val) => {
          search.value = val;
        },
        size: "large",
        placeholder: "Пошук за коментарем",
        "prefix-icon": Search,
        style: "width: 100%; margin-top:15px;",
        onInput: () => {
          debouncedChange.value();
        },
      }),
    ],
  });
};

const getBits = () => {
  setting.value.comps.curComp = "eBits";
  setting.value.titleTable = setting.value.tables["tabBits"].title;
};

const changeTab = (tab) => {
  changeSettingUser({ nameTab: tab });
};

const shortCuts = [
  {
    text: "- день",
    value: () => {
      const end = new Date();
      const start = new Date();
      start.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
      return [start, end];
    },
  },
  {
    text: "- тиждень",
    value: () => {
      const end = new Date();
      const start = new Date();
      start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
      return [start, end];
    },
  },
  {
    text: "- місяць",
    value: () => {
      const end = new Date();
      const start = new Date();
      start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
      return [start, end];
    },
  },
];

const createPeresort = () => {
  setting.value.dialog["createPeresort"].initiator = "createPeresort";
  setting.value.dialog["createPeresort"].visible = true;
};

const changeCheck = async (row, atr) => {
  const _tabl = setting.value.tables["tabTransaction"];
  const curTransaction = _tabl.data.find((el) => el.id_T == row.id_T);
  const curVal = +curTransaction[`is${atr}`];

  const response = await HTTP.get("", {
    params: {
      _method: "setAccessTransaction",
      _id_T: row.id_T,
      _atr: `is${atr}`,
      _val: !curVal,
    },
  });

  if (response.data.isSuccesfull) {
    curTransaction[`is${atr}`] = !curVal;
  } else {
    ElMessage("Помилка зміни");
  }
};

onActivated(async () => {
  setPagination.sizePage = getSettingUser.value.countTrans;
  await getPunktCur();
  await getTransaction();

  activeName.value = getSettingUser.value.nameTab.length
    ? getSettingUser.value.nameTab
    : punkts.value[0]["name"];

  debouncedChange.value = _.debounce(() => {
    getTransaction();
  }, 1000);
});

onUpdated(async () => {
  setPagination.sizePage = getSettingUser.value.countTrans;
  await getTransaction();
});

onUnmounted(() => {
  debouncedChange.value.cancel();
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

.el-statistic {
  --el-statistic-content-font-size: 28px;
}

.statistic-card {
  height: 100%;
  padding: 0px;
  border-radius: 4px;
  background-color: var(--el-bg-color-overlay);
}

.statistic-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  font-size: 12px;
  color: var(--el-text-color-regular);
  margin-top: 5px;
}

.statistic-footer .footer-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.statistic-footer .footer-item span:last-child {
  display: inline-flex;
  align-items: center;
  margin-left: 5px;
}

.green {
  color: var(--el-color-success);
}
.red {
  color: var(--el-color-error);
}

.el-row {
  margin-bottom: 8px;
}

.el-card.panel {
  height: 120px;
}

.popconfirm-base-box .box-item {
  width: 110px;
  margin-top: 10px;
}
</style>
