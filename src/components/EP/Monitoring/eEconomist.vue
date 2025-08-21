<template>
  <el-space :size="10" style="margin: 0px 0 0px 0">
    <el-card>
      <el-space :size="10" wrap>
        <el-select
          v-model="checkManeger"
          multiple
          clearable
          collapse-tags
          placeholder="оберіть менеджера..."
          popper-class="custom-header"
          :max-collapse-tags="1"
          style="width: 240px"
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
          style="width: 240px"
        >
          <el-option
            v-for="item in options"
            :key="item.idV"
            :label="item.name"
            :value="item.idV"
          />
        </el-select>

        <el-radio-group v-model="userLayout" @change="changeLayout()">
          <el-radio-button value="false" label="+м" />
          <el-radio-button value="true" label="-м" />
        </el-radio-group>

        <el-radio-group v-model="punktLayout" @change="changeLayout()">
          <el-radio-button value="false" label="+п" />
          <el-radio-button value="true" label="-п" />
        </el-radio-group>

        <el-button-group>
          <el-button type="success" :icon="Tickets" @click="loadReport()">
            Формувати звіт
          </el-button>

          <el-button
            type="primary"
            plain
            :icon="Refresh"
            @click="getETransaction()"
          >
            Оновити
          </el-button>
        </el-button-group>
      </el-space>
    </el-card>

    <el-card style="max-height: 75px">
      <el-row :gutter="10">
        <el-col :span="3">
          <el-switch v-model="isPeriod" @change="getETransaction" />
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
            style="width: 210px; margin-left: -10px"
          />
        </el-col>
      </el-row>
    </el-card>
  </el-space>

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
        <div style="padding: 20px; background: #c6e2ff69">
          <div style="margin: 0 0 20px 40px">{{ nameColumnT }}</div>

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
                <el-table
                  :data="scope.row.listTransaction"
                  :default-sort="{ prop: 'date', order: 'ascending' }"
                  style="margin-left: 4%; background: #124578; width: 95%"
                  stripe
                  border
                >
                  <el-table-column
                    type="index"
                    v-if="setting.displaySize == 'large'"
                  />

                  <el-table-column label="врахувати" min-width="15">
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

                  <el-table-column prop="date" sortable min-width="25">
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
                      {{ parseFloat(props.row.suma).toLocaleString("ua") }} грн.
                    </template>
                  </el-table-column>
                </el-table>
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
const checkOperation = ref();
const listManeger = ref([]);
const options = ref([]);
const loading = ref(false);
const valueDate = ref([new Date(), new Date()]);
const isPeriod = ref(true);

const userLayout = ref("false");
const punktLayout = ref("false");
const forceRenderUser = ref(0);

watch(
  () => [checkOperation.value],
  () => getETransaction()
);

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabEconomist"];

  //   const _tabl = {
  //     data: [
  //       {
  //         id_U: "30",
  //         pib: "Ратова Євгенія",
  //         idV: "1",
  //         name: "Заготовка",
  //         listPunkt: [
  //           {
  //             id: "11",
  //             namePunkt: "ДМС_ЦМ",
  //             listTransaction: [
  //               { idT: 1, comment: "загот1", date: "18.08.2025", summa: 52 },
  //             ],
  //           },
  //           {
  //             id: "18",
  //             namePunkt: "Киев_Отрадный ",
  //             listTransaction: [
  //               { idT: 1, comment: "загот2", date: "18.08.2025", summa: 546 },
  //             ],
  //           },
  //           {
  //             id: "29",
  //             namePunkt: "Черноморск",
  //             listTransaction: [
  //               { idT: 1, comment: "загот3", date: "18.08.2025", summa: 986 },
  //             ],
  //           },
  //         ],
  //       },
  //     ],
  //   };
  return _tabl.data;
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
          options.value.push({ idV: 21, name: `${el.name}  на склад` });
          options.value.push({ idV: 22, name: `${el.name}  на покупця` });
        }
      });

    checkOperation.value = options.value[0].idV;

    if (+getSettingUser.value.isShowMes) {
      ElMessage.success("Операції оновлені");
    }
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const getETransaction = async () => {
  try {
    if (!checkManeger.value.length) {
      ElMessage.success("Оберіть менеджера");
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
      ElMessage.success(`Список з пунктами та транзакціями ${info}`);
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

    const response = await HTTP.post("", {
      _method: "loadReport",
      _id_U: getCurUser.value.id,
      _arTransaction: arTransaction,
    });

    if (response.data.isSuccesfull) {
      ElMessage.success("Звіт сформовано");
    } else {
      ElMessage.error("Звіт не сформовано");
    }
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

onActivated(async () => {
  await getManeger();
  //   await getMonitoring();
  await getVidOperation();
});
</script>

<style></style>
