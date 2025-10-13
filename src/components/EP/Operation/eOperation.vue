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

      <el-space style="margin-top: -5px">
        <el-card v-if="setting.displaySize == 'large'">
          <el-space :size="0" style="padding: 0px 0px">
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
                  <span>з минулим днем: </span>
                  <span
                    :class="[kassa.percent > 0 ? 'green' : 'red']"
                    :title="`${kassa.oldSumma} грн.`"
                  >
                    {{
                      parseFloat(
                        (kassa.summa - kassa.oldSumma).toFixed(1)
                      ).toLocaleString("rus")
                    }}
                    грн. ({{ kassa.percent }}%)
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
            style="width: 90px; height: 90px; margin: -10px -10px -10px 10px"
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

        <el-card
          v-if="setting.displaySize == 'large'"
          style="height: 115px"
          :body-style="{ padding: '12px 20px 15px 20px' }"
        >
          <el-row :gutter="0">
            <el-col :span="3">
              <el-switch v-model="isPeriod" @change="getTransaction" />
            </el-col>
            <el-col :span="2"> </el-col>

            <el-col :span="19">
              <el-date-picker
                v-model="valueDate"
                type="daterange"
                format="DD.MM.YYYY"
                :start-placeholder="getDate"
                :end-placeholder="getDate"
                :disabled="!isPeriod"
                :shortcuts="shortCuts"
                @change="getTransaction"
                style="width: 210px; padding: 0px 10px; margin-left: -10px"
              />
            </el-col>
          </el-row>
          <el-row :gutter="10">
            <el-col :span="20">
              <el-input
                v-model="search"
                size="normal"
                style="width: 250px"
                placeholder="Пошук за коментарем"
                @input="debouncedChange"
                :prefix-icon="Search"
              />
            </el-col>
            <el-col :span="4">
              <el-button
                :type="isFilterOper || isFilterMat ? 'warning' : 'info'"
                :icon="Filter"
                @click="loadFiltrAddit"
              />
            </el-col>
          </el-row>
        </el-card>

        <!-- <el-space direction="vertical">
             <el-button-group> 
        -->
        <component
          :is="setting.displaySize == 'large' ? ElSpace : ElButtonGroup"
          v-bind="{ direction: 'vertical' }"
        >
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
            :style="{ width: setting.displaySize == 'large' ? '150px' : '' }"
            @click="newTransaction()"
            >Нова операція
          </el-button>

          <el-button
            type="info"
            :icon="Sort"
            :style="{ width: setting.displaySize == 'large' ? '150px' : '' }"
            @click="createPeresort()"
          >
            Пересорт
          </el-button>

          <el-button
            type="primary"
            plain
            :icon="Refresh"
            :style="{ width: setting.displaySize == 'large' ? '150px' : '' }"
            @click="getTransaction()"
          >
            Оновити
          </el-button>
        </component>
      </el-space>

      <!-- <el-scrollbar height="600px"> -->

      <el-table
        :data="filterTable"
        v-loading="loading"
        :default-expand-all="+viewOperation"
        stripe
      >
        <el-table-column type="expand" min-width="15">
          <template #default="props">
            <div class="expand-content">
              <el-tag type="success" style="margin: -10px 0 10px 0px">
                Транзакція за операцією: <b>{{ props.row.comment }} </b> /
                {{ props.row.date }} - {{ props.row.time }}
              </el-tag>

              <el-table
                :data="props.row.listOperMain"
                border
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

        <el-table-column min-width="15" v-if="setting.displaySize == 'large'">
          <template #header>
            <span>в 1С</span>
          </template>
          <template #default="scope">
            <el-icon
              size="20"
              v-if="scope.row.isLoadReport"
              color="var(--el-border-color)"
              ><CircleCheckFilled
            /></el-icon>
          </template>
        </el-table-column>

        <el-table-column
          prop="date"
          :min-width="setting.displaySize == 'large' ? 45 : 33"
          wrap
        >
          <template #header>
            <span style="margin-left: 10px">Дата</span>
          </template>
          <template #default="scope">
            <div>
              <el-tooltip placement="top">
                <template #content>
                  id_T : {{ scope.row.id_T }}<br />
                  дата створення : {{ scope.row.dateCreate }}<br />
                  час створення : {{ scope.row.time }}<br />
                  автор : {{ scope.row.PIB }}
                </template>
                <div>
                  <el-icon
                    v-if="
                      scope.row.isLoadReport && setting.displaySize == 'small'
                    "
                    size="20"
                    color="var(--el-border-color)"
                    ><CircleCheckFilled
                  /></el-icon>
                  {{ scope.row.date }}
                  {{
                    setting.displaySize == "large"
                      ? ` - ${scope.row.time}`
                      : `\n ${scope.row.time}`
                  }}
                </div>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>

        <el-table-column
          label="Коментар"
          prop="comment"
          v-if="setting.displaySize == 'large'"
        />

        <el-table-column
          :label="setting.displaySize == 'large' ? 'Сума' : 'Дані'"
          min-width="40"
        >
          <template #default="scope">
            <div style="text-align: left">
              <span
                >{{
                  setting.displaySize == "small" ? `${scope.row.comment}` : ""
                }}
              </span>
              <div style="background: rgb(167 235 167); text-align: center">
                {{ `${parseFloat(scope.row.suma).toLocaleString("ua")} грн.` }}
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column
          label="Дії"
          :min-width="setting.displaySize == 'large' ? 45 : 20"
        >
          <template #default="scope">
            <el-button-group
              class="ml-4"
              v-if="
                setting.dialog['editManeger'].chooseUser == 'user' ||
                (setting.dialog['editManeger'].chooseUser == 'maneger' &&
                  +getCurUser.listAccess[9])
              "
            >
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
              v-if="+getCurUser.listAccess[5] && setting.displayWidth >= 800"
              inline-prompt
              active-text="edit"
              inactive-text="edit"
              :modelValue="scope.row.isEdit == 1"
              @change="changeCheck(scope.row, 'Edit')"
              style="margin-left: 10px"
            />
            <el-switch
              v-if="+getCurUser.listAccess[6] && setting.displayWidth >= 800"
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
        style="margin-top: 15px; float: right"
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
  Filter,
} from "@element-plus/icons-vue";
import {
  ElCascader,
  ElMessage,
  ElMessageBox,
  ElNotification,
  ElSwitch,
  ElDatePicker,
  ElInput,
  ElSpace,
  ElButtonGroup,
  ElRadioGroup,
  ElRadioButton,
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
const listMaterial = ref([]);
const listOperation = ref([]);
const checkMaterial = ref([]);
const checkOperation = ref([]);

const valueDate = ref([new Date(), new Date()]);
const punkts = ref([]);
const activeName = ref("");
const isPeriod = ref(false);
const isFilterOper = ref(false);
const isFilterMat = ref(false);
const curDate = ref(new Date());
const setPagination = reactive({
  currentPage: 1,
  sizePage: 5,
  total: 1,
});
const debouncedChange = ref();
const loading = ref(true);
const viewOperation = ref("0");
const kassa = reactive({
  summa: 0,
  oldSumma: 0,
  percent: 0,
});

watch(
  () => [activeName.value, setting.value.dialog["editOperation"].visible],
  () => getTransaction()
);

watch(
  () => [
    setting.value.dialog["editManeger"].chooseUser,
    setting.value.dialog["editManeger"].idManeger,
  ],
  () => getPunktCur()
);

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

    setting.value.titleTable = `${setting.value.tables["tabTransaction"].title} ${nameUser}`;
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
  const _tabl = setting.value.tables["tabTransaction"].data.map((el) => {
    return {
      ...el,
      listOperMain: el.listOper.filter((oper) => +oper.isMoveKassa != -1),
    };
  });

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

  //return _tabl.data;

  return isFilterOper.value || isFilterMat.value
    ? _tabl
        .map((el) => {
          return {
            ...el,
            listOperMain: el.listOperMain.filter(
              (oper) =>
                ((isFilterOper.value && oper.id_V == checkOperation.value[0]) ||
                  !isFilterOper.value) &&
                ((isFilterMat.value && oper.id_M == checkMaterial.value[1]) ||
                  !isFilterMat.value)
            ),
          };
        })
        .filter((trans) => trans.listOperMain.length)
    : _tabl;
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
        style: "width: 100%; margin-top:10px;",
        onInput: () => {
          debouncedChange.value();
        },
      }),
    ],
  });
};

const loadFiltrAddit = async () => {
  if (!listOperation.value.length) {
    const response = await HTTP.get("", {
      params: { _method: "getOperation", _id_P: 30 },
    });

    response.data.forEach((oper) => {
      listOperation.value.push({
        value: oper.value.id,
        label: oper.label,
      });

      oper.children.forEach((kat) => {
        const listMat = kat.children.map((mat) => {
          return { value: mat.value.id, label: mat.label };
        });

        if (!listMaterial.value.some((el) => el.value == kat.value.id)) {
          listMaterial.value.push({
            value: kat.value.id,
            label: kat.label,
            children: listMat,
          });
        }
      });
    });
  }

  ElMessageBox({
    title: "Додатковий фільтр",
    confirmButtonText: "Закрити",
    message: () => [
      h(ElSwitch, {
        style: "margin: 10px 5px 10px 20px;",
        modelValue: isFilterOper.value,
        "onUpdate:modelValue": (val) => {
          isFilterOper.value = val;
        },
      }),
      h(ElCascader, {
        modelValue: checkOperation.value,
        "onUpdate:modelValue": (val) => {
          checkOperation.value = val;
        },
        size: "normal",
        // disabled: !isFilterOper.value,
        options: listOperation.value,
        placeholder: "оберіть операцію...",
        "prefix-icon": Search,
        style: "width: 73%; margin: 10px 20px 10px 20px;",
      }),
      h(ElSwitch, {
        style: "margin: 10px 5px 10px 20px;",
        modelValue: isFilterMat.value,
        "onUpdate:modelValue": (val) => {
          isFilterMat.value = val;
        },
      }),
      h(ElCascader, {
        modelValue: checkMaterial.value,
        "onUpdate:modelValue": (val) => {
          checkMaterial.value = val;
        },
        size: "normal",
        // disabled: !isFilterMat.value,
        options: listMaterial.value,
        placeholder: "оберіть матеріал...",
        "prefix-icon": Search,
        style: "width: 73%; margin: 10px 20px 10px 20px;",
      }),
      h(
        ElRadioGroup,
        {
          modelValue: viewOperation.value,
          "onUpdate:modelValue": (val) => (viewOperation.value = val),
          style: "margin: 10px 5px 10px 80px;",
          onChange: () => {
            getTransaction();
          },
        },
        [
          h(ElRadioButton, { label: "1" }, () => "Розкрити транзакції "),
          h(ElRadioButton, { label: "0" }, () => "Закрити транзакції"),
        ]
      ),
    ],
    beforeClose: (action, instance, done) => {
      // if (action === "confirm") {
      //   ElMessage.success(checkOperation.value[0]);
      //   ElMessage.success(checkMaterial.value[0]);
      //   ElMessage.success(checkMaterial.value[1]);
      // }

      done(); // закрываем окно вручную
      ElMessage.success(
        isFilterOper.value || isFilterMat.value
          ? "Додатковий фільтр включений"
          : "Додатковий фільтр вимкнений"
      );
    },
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

<style scoped>
.demo-tabs > .el-tabs__content {
  padding: 15px;
  color: #6b778c;
  font-size: 32px;
  /* font-weight: 600; */
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
  margin-top: 0px;
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

.expand-content {
  padding: 20px;
  background: #4caf5045;
  border-radius: 25px;
  margin: 0px 10px;
}

.el-table {
  margin: 5px 0px 0px 0px;
}

@media (max-width: 768px) {
  .el-button {
    width: 50% !important;
    margin-bottom: 2px;
  }
  .el-input,
  .el-date-picker {
    width: 100% !important;
  }

  .el-space {
    margin: -20px 10px 10px 10px;
  }

  .el-table {
    font-size: 9pt;
    /* font-weight: normal; */
    text-align: left;
    line-height: 1.2;
    padding: 0;
    margin-top: 0px;
  }

  .el-tab-pane {
    margin: 0px -5px 0px -5px;
  }
}
</style>
