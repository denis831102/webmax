<template>
  <div class="card">
    <el-row>
      <el-col :xs="10" :sm="10" :md="10" :lg="9" :xl="9">
        <el-select
          v-model="checkManeger"
          multiple
          clearable
          collapse-tags
          placeholder="оберіть менеджера..."
          popper-class="custom-header"
          :max-collapse-tags="1"
          style="width: 45%; margin-right: 10px"
          @change="getETransaction()"
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

        <el-select
          v-model="checkOperation"
          placeholder="оберіть операцію..."
          popper-class="custom-header"
          :max-collapse-tags="1"
          style="width: 45%"
        >
          <el-option
            v-for="item in options"
            :key="item.idV"
            :label="item.name"
            :value="item.idV"
          />
        </el-select>
      </el-col>
      <el-col :xs="8" :sm="8" :md="8" :lg="5" :xl="5">
        <el-radio-group
          v-model="userLayout"
          @change="changeLayout()"
          style="width: 45%"
        >
          <el-radio-button value="false" label="+м" />
          <el-radio-button value="true" label="-м" />
        </el-radio-group>

        <el-radio-group
          v-model="punktLayout"
          @change="changeLayout()"
          style="margin-left: 5px; width: 45%"
        >
          <el-radio-button value="false" label="+п" />
          <el-radio-button value="true" label="-п" />
        </el-radio-group>
      </el-col>
      <el-col :xs="6" :sm="6" :md="6" :lg="3" :xl="3">
        <el-button
          type="success"
          :icon="Tickets"
          @click="loadReport()"
          style="width: 90%"
        >
          Формувати звіт
        </el-button>
      </el-col>
    </el-row>

    <el-row>
      <el-col :xs="10" :sm="10" :md="10" :lg="9" :xl="9">
        <div style="text-align: center">
          <el-switch v-model="isPeriod" @change="getETransaction" />
          <el-date-picker
            v-model="valueDate"
            type="daterange"
            format="DD.MM.YYYY"
            :start-placeholder="getDate"
            :end-placeholder="getDate"
            :disabled="!isPeriod"
            :shortcuts="shortCuts"
            style="width: 210px; margin-left: 10px"
            @change="getETransaction"
          />
        </div>
      </el-col>
      <el-col :xs="8" :sm="8" :md="8" :lg="5" :xl="5">
        <el-radio-group v-model="isLoadReport">
          <el-radio-button label="усі" value="all" />
          <el-radio-button label="нові" value="new" />
          <el-radio-button label="в 1С" value="loaded" />
        </el-radio-group>
      </el-col>
      <el-col :xs="6" :sm="6" :md="6" :lg="3" :xl="3">
        <el-button
          type="primary"
          plain
          :icon="Refresh"
          @click="getETransaction()"
          style="width: 90%"
        >
          Оновити
        </el-button>
      </el-col>
    </el-row>
  </div>

  <el-table
    :data="filterTable"
    row-style="background:#f4f4f5"
    v-loading="loading"
    stripe
    :key="forceRenderUser"
    :default-expand-all="userLayout == 'true'"
  >
    <el-table-column type="expand">
      <template #default="props">
        <div>
          <el-table
            :data="props.row.listPunkt"
            v-if="props.row.listPunkt.length"
            row-style="background:#bad7da;"
            border="true"
            :show-header="false"
            style="margin-left: 2%; width: 98%"
            :default-expand-all="punktLayout == 'true'"
          >
            <el-table-column style="width: 98%" type="expand">
              <template #default="scope">
                <div class="expand-content">
                  <el-tooltip placement="top"> </el-tooltip>

                  <el-table
                    :data="scope.row.listTransaction"
                    :default-sort="{ prop: 'date', order: 'ascending' }"
                    style="margin-left: 2%; background: #124578; width: 95%"
                    stripe
                    border
                  >
                    <el-table-column
                      type="index"
                      v-if="setting.displaySize == 'large'"
                    />

                    <el-table-column label="врахувати" min-width="20">
                      <template #default="transaction">
                        <el-switch
                          :modelValue="transaction.row.isLoad == 1"
                          @change="changeCheck(transaction.row)"
                          inline-prompt
                          style="
                            --el-switch-on-color: #13ce66;
                            --el-switch-off-color: #e48c18;
                          "
                          active-text="+"
                          inactive-text="-"
                        />
                      </template>
                    </el-table-column>

                    <el-table-column prop="date" sortable min-width="20">
                      <template #header>
                        <el-icon><Calendar /></el-icon>
                        <span style="margin-left: 10px">Дата</span>
                      </template>

                      <template #default="index">
                        <div style="display: flex; align-items: center">
                          <span style="margin-left: 10px">
                            {{ index.row.date }}</span
                          >
                        </div>
                      </template>
                    </el-table-column>

                    <el-table-column label="Коментар" prop="comment">
                    </el-table-column>

                    <el-table-column label="Сума" prop="suma">
                      <template #default="props">
                        {{ parseFloat(props.row.suma).toLocaleString("ua") }}
                        грн.
                      </template>
                    </el-table-column>
                  </el-table>
                </div>
              </template>
            </el-table-column>

            <el-table-column label="Назва пункта" prop="namePunkt" />
          </el-table>
        </div>
      </template>
    </el-table-column>

    <el-table-column type="index" v-if="setting.displaySize == 'large'" />

    <el-table-column>
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.pib }}</span>
        </div>
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
/* eslint-disable */
import { inject, ref, computed, onActivated, watch } from "vue";
import { useStore } from "vuex";
import { User, Refresh, Tickets } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP, loadFile } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
const checkAll = ref(false);
const indeterminate = ref(false);
const checkManeger = ref([]);
const checkOperation = ref(null);
const listManeger = ref([]);
const options = ref([]);
const loading = ref(false);
const valueDate = ref([new Date(), new Date()]);
const isPeriod = ref(true);

const userLayout = ref("false");
const punktLayout = ref("false");
const forceRenderUser = ref(0);
const isLoadReport = ref("new");

watch(
  () => [checkOperation.value],
  () => getETransaction()
);

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabEconomist"].data;

  return _tabl
    .map((user) => {
      return {
        ...user,
        listPunkt: user.listPunkt
          .map((punkt) => {
            return {
              ...punkt,
              listTransaction: punkt.listTransaction.filter(
                (tran) =>
                  tran.id_M != 40 &&
                  ((isLoadReport.value == "new" && tran.isLoadReport == 0) ||
                    isLoadReport.value == "all" ||
                    (isLoadReport.value == "loaded" && tran.isLoadReport == 1))
              ),
            };
          })
          .filter((el) => el.listTransaction.length),
      };
    })
    .filter((el) => el.listPunkt.length);
});

const getVidOperation = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getVidOperation",
      },
    });

    options.value = [];

    response.data
      .filter((el) => {
        return el.idV != 7 && el.idV != 4; // ![4, 7].includes(el.idV)
      })
      .forEach((el) => {
        if (el.idV != 2) {
          if (el.idV == 6) {
            options.value.push({ idV: 6, name: `Сортування` });
          } else {
            options.value.push(el);
          }
        } else {
          options.value.push({ idV: 21, name: `${el.name} на склад` });
          options.value.push({ idV: 22, name: `${el.name} на покупця` });
        }
      });

    if (!checkOperation.value) {
      checkOperation.value = options.value[0].idV;
    }

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Операції оновлені");
    }
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const changeLoadReport = () => {
  isLoadReport({ themaColor: themaColor.value });
};

const getETransaction = async () => {
  try {
    if (!checkManeger.value.length) {
      ElMessage.error("Оберіть менеджера");
      setting.value.tables["tabEconomist"].data = [];
      return;
    }

    loading.value = true;

    const response = await HTTP.post("", {
      _method: "getETransaction",
      _id_U: getCurUser.value.id,
      _checkManeger: checkManeger.value,
      _checkOperation: checkOperation.value,
      _date_l: formatDate(valueDate.value[0], "eng"),
      _date_r: formatDate(valueDate.value[1], "eng"),
      _isPeriod: isPeriod.value ? 1 : 0,
    });

    setting.value.tables["tabEconomist"].data = response.data.ar_data;
    loading.value = false;

    if (+getSettingUser.value.isShowMes) {
      const info = setting.value.tables["tabEconomist"].data.length
        ? "ЗАВАНТАЖЕНИЙ"
        : "ПУСТИЙ";
      const type = setting.value.tables["tabEconomist"].data.length
        ? "success"
        : "error";
      ElMessage({
        message: `Список з пунктами та транзакціями ${info}`,
        type,
      });
    }
  } catch (e) {
    ElMessage.error("Помилка завантаження списку");
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
  getETransaction();
};

const nameColumnT = computed(() => {
  const nameO = options.value
    .find((el) => {
      return el.idV == checkOperation.value;
    })
    .name.toUpperCase();
  return `Транзакції за операцією "${nameO}"`;
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

const changeCheck = (rowT) => {
  const _tabl = setting.value.tables["tabEconomist"];
  const curTrans = _tabl.data
    .find((el) => el.id_U == rowT.id_U)
    .listPunkt.find((el) => el.id_P == rowT.id_P)
    .listTransaction.find((el) => el.id_T == rowT.id_T);
  const curVal = +curTrans[`isLoad`];
  curTrans[`isLoad`] = !curVal;
};

const changeLayout = () => {
  forceRenderUser.value++;
};

const loadReport = async () => {
  try {
    const _tabl = setting.value.tables["tabEconomist"];
    const arTransaction = [];

    _tabl.data.forEach((maneger) => {
      maneger.listPunkt.forEach((punkt) => {
        punkt.listTransaction.forEach((transaction) => {
          if (transaction.isLoad) {
            arTransaction.push(transaction.id_T);
          }
        });
      });
    });

    if (!arTransaction.length) {
      ElMessage.error("Транзакції для обробки відсутні");
      return;
    }

    const nameOperation = options.value.find(
      (el) => el.idV == checkOperation.value
    ).name;

    const response = await HTTP.post("", {
      _method: "loadReport",
      _id_U: getCurUser.value.id,
      _nameReport: "economist",
      _checkOperation: checkOperation.value,
      _nameOperation: nameOperation,
      _arTransaction: arTransaction,
    });

    if (response.data.isSuccesfull) {
      loadFile(
        response.data.fileName,
        response.data.content,
        response.data.mime
      );

      ElMessage.success("Звіт сформовано");
    } else {
      ElMessage.error("Звіт не сформовано");
    }
  } catch (e) {
    ElMessage("Помилка завантаження звіту...");
  }
};

onActivated(async () => {
  await getManeger();
  //   await getMonitoring();
  await getVidOperation();
});
</script>

<style>
.expand-content {
  padding: 10px;
  background: #4caf5045;
  border-radius: 25px;
  margin: 0px 5px;
}

.el-row {
  margin-bottom: 10px;
}

.el-row:last-child {
  margin-bottom: 0;
}

.card {
  margin-left: 0px;
  box-shadow: inset -1px 0px 17px 1px #76c876;
}
</style>
