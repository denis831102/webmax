<template>
  <div class="card">
    <el-row wrap>
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-switch v-model="isPeriod" @change="getMoney" style="margin-left: 5px" />
        <el-date-picker
          v-model="valueDate"
          type="daterange"
          format="DD.MM.YYYY"
          :start-placeholder="getDate"
          :end-placeholder="getDate"
          :disabled="!isPeriod"
          :shortcuts="shortCuts"
          @change="getMoney"
          style="width: 80%; margin-left: 5px"
        />
      </el-col>

      <el-col :xs="12" :sm="6" :md="4" :lg="4">
        <el-button
          type="primary"
          style="margin-left: 5px; width: 90%"
          @click="getMoney()"
          plain
          :icon="Refresh"
        >
          Оновити
        </el-button>
      </el-col>

      <el-col :xs="12" :sm="6" :md="4" :lg="4">
        <el-button type="success" style="margin-left: 5px" @click="isSummaryVisible = !isSummaryVisible">
          {{ isSummaryVisible ? "Приховати підсумок" : "Показати підсумок" }}
        </el-button>
      </el-col>

      <el-col :xs="12" :sm="12" :md="5" :lg="5">
        <el-radio-group v-model="punktLayout" @change="changeLayout()" style="margin-left: 5px">
          <el-radio-button value="false" label="+Пункт" />
          <el-radio-button value="true" label="-Пункт" />
        </el-radio-group>
      </el-col>

      <el-col :xs="12" :sm="12" :md="5" :lg="5">
        <el-radio-group v-model="ruxhLayout" @change="changeLayout()" style="margin-left: 5px">
          <el-radio-button value="false" label="+ Рух коштів" />
          <el-radio-button value="true" label="- Рух коштів" />
        </el-radio-group>
      </el-col>
    </el-row>
  </div>

  <div
    class="card"
    style="box-shadow: inset 14px 4px 25px 9px var(--el-color-success)"
    v-show="isSummaryVisible"
  >
    <h3 style="margin-bottom: 10px" align="center">Підсумок по надходженням та витратам (всі пункти)</h3>
    <el-table :data="summaryByMoney">
      <el-table-column prop="nameVidKassa" align="center"> </el-table-column>
      <el-table-column align="left">
        <template #default="scope"> {{ scope.row.sumVidKassa.toLocaleString("uk-UA") }} грн </template>
      </el-table-column>
    </el-table>
  </div>

  <el-table :data="filterTable" row-style="background:#f4f4f5">
    <el-table-column>
      <template #default="user">
        <div style="padding: 10px; background: #c6e2ff69">
          <el-table
            :data="user.row.listPunkt"
            :key="forceRenderUser"
            row-style="background:#bad7da;"
            border="true"
            :show-header="false"
            style="margin-left: 2%; width: 98%"
            :default-expand-all="punktLayout == 'true'"
          >
            <el-table-column style="width: 98%" type="expand">
              <template #default="scope">
                <div class="expand-contentBig">
                  <el-table
                    :data="scope.row.listVidKasa"
                    :default-sort="{ prop: 'date', order: 'ascending' }"
                    style="margin-left: 5%; background: #124578; width: 95%"
                    :show-header="true"
                    stripe
                    border
                    :default-expand-all="ruxhLayout == 'true'"
                  >
                    <el-table-column style="width: 98%" type="expand">
                      <template #default="scope">
                        <el-table
                          :data="scope.row.listInKassa"
                          :default-sort="{ prop: 'date', order: 'ascending' }"
                          style="margin-left: 4%; background: #124578; width: 96%"
                          stripe
                          border="true"
                          :show-header="false"
                        >
                          <el-table-column>
                            <template #default="scope">
                              <div style="display: flex; align-items: center">
                                <span style="margin-left: 5%">{{ scope.row.nameN }}</span>
                              </div>
                            </template>
                          </el-table-column>

                          <el-table-column label="Сума, грн " prop="suma">
                            <template #default="props">
                              <div>
                                {{ parseFloat(props.row.suma).toLocaleString("ru") }}
                                {{ props.row.unit }}
                              </div>
                            </template>
                          </el-table-column>
                        </el-table>
                      </template>
                    </el-table-column>

                    <el-table-column label="Рух коштів" prop="nameVidKassa" />

                    <el-table-column label="Сума, грн " prop="sumVidKassa">
                      <template #default="props">
                        <div>
                          {{ parseFloat(props.row.sumVidKassa).toLocaleString("ru") }}
                          {{ props.row.unit }}
                        </div>
                      </template>
                    </el-table-column>
                  </el-table>
                </div>
              </template>
            </el-table-column>

            <el-table-column label="Назва пункта" prop="namePunkt" />

            <el-table-column>
              <template #default="scope">
                <div style="display: flex; align-items: center">
                  <el-icon><Money /></el-icon>
                  <span style="margin-left: 10px"
                    >{{ parseFloat(scope.row.sumaP).toLocaleString("rus") }} {{ scope.row.unit }}</span
                  >
                </div>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </template>
    </el-table-column>

    <!-- <el-table-column>
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.pib }}</span>
        </div>
      </template>
    </el-table-column> -->

    <!-- <el-table-column>
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Money /></el-icon>
          <span style="margin-left: 10px"
            >{{ parseFloat(scope.row.sumaU).toLocaleString("rus") }} {{ scope.row.unit }}</span
          >
        </div>
      </template>
    </el-table-column> -->
  </el-table>
</template>

<script setup>
//отключает наблюдатель за ошибками
import { inject, ref, computed } from "vue";
import { useStore } from "vuex";
import { Refresh, Money } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
const valueDate = ref([new Date(), new Date()]);
const isPeriod = ref(true);
const loading = ref(false);
const punktLayout = ref("false");
const ruxhLayout = ref("false");
const forceRenderUser = ref(0);
const isSummaryVisible = ref(false);

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabZvitMoneyM"];

  // const _tabl = {
  //   data: [
  //     {
  //       id_U: "30",
  //       pib: "Ратова Євгенія",
  //       listPunkt: [
  //         {
  //           id: "11",
  //           namePunkt: "ДМС_ЦМ",
  //           listVidKasa: [
  //             {
  //               id: "4",
  //               nameVidKassa: "приход",
  //               listInKassa: [
  //                 {
  //                   id_N: "89",
  //                   nameN: "поповнення",
  //                   suma: "10000",
  //                 },
  //               ],
  //               sumVidKassa: "2000",
  //             },

  //             {
  //               id: "5",
  //               nameVidKassa: "расход",
  //               listInKassa: [
  //                 {
  //                   id_N: "45",
  //                   nameN: "закупівля матеріала",
  //                   suma: "8000",
  //                 },
  //               ],
  //               sumVidKassa: "2000",
  //             },
  //           ],
  //         },
  //         {
  //           id: "18",
  //           namePunkt: "Киев_Отрадный ",
  //           listVidKasa: [
  //             {
  //               id: "4",
  //               nameVidKassa: "приход",
  //               listInKassa: [
  //                 {
  //                   id_N: "891",
  //                   nameN: "поповнення",
  //                   suma: "5000",
  //                 },
  //               ],
  //               sumVidKassa: "2000",
  //             },
  //             {
  //               id: "5",
  //               nameVidKassa: "расход",
  //               listInKassa: [
  //                 {
  //                   id_N: "45",
  //                   nameN: "закупівля матеріала",
  //                   suma: "2000",
  //                 },
  //               ],
  //               sumVidKassa: "2000",
  //             },
  //           ],
  //         },
  //         {
  //           id: "29",
  //           namePunkt: "Черноморск",
  //           listVidKasa: [
  //             {
  //               id: "4",
  //               nameVidKassa: "приход",
  //               listInKassa: [
  //                 {
  //                   id_N: "89",
  //                   nameN: "поповнення",
  //                   suma: "3000",
  //                 },
  //                 {
  //                   id_N: "91",
  //                   nameN: "надходження за продаж",
  //                   suma: "500",
  //                 },
  //               ],
  //               sumVidKassa: "5000",
  //             },
  //             {
  //               id: "5",
  //               nameVidKassa: "расход",
  //               listInKassa: [
  //                 {
  //                   id_N: "45",
  //                   nameN: "закупівля матеріала",
  //                   suma: "2800",
  //                 },
  //               ],
  //               sumVidKassa: "4800",
  //             },
  //           ],
  //         },
  //       ],
  //     },
  //   ],
  // };
  return _tabl.data;
});

const changeLayout = () => {
  forceRenderUser.value++;
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

const summaryByMoney = computed(() => {
  const money = {};
  // Проходим по всем менеджерам/строкам в основной таблице
  filterTable.value.forEach((manager) => {
    // Проходим по всем пунктам внутри менеджера
    manager.listPunkt?.forEach((punkt) => {
      punkt.listVidKasa.forEach((nameVid) => {
        const name = nameVid.nameVidKassa;
        if (!money[name]) {
          money[name] = {
            nameVidKassa: name,
            sumVidKassa: 0,
          };
        }
        money[name].sumVidKassa += parseFloat(nameVid.sumVidKassa || 0);
      });
    });
  });
  return Object.values(money);
});

const getMoney = async () => {
  try {
    loading.value = true;
    const response = await HTTP.post("", {
      _method: "getMoney",
      _id_U: getCurUser.value.id,
      _checkManeger: [getCurUser.value.id],
      _date_l: formatDate(valueDate.value[0], "eng"),
      _date_r: formatDate(valueDate.value[1], "eng"),
      _isPeriod: isPeriod.value ? 1 : 0,
    });

    setting.value.tables["tabZvitMoneyM"].data = response.data.ar_data;
    loading.value = false;

    if (+getSettingUser.value.isShowMes) {
      let limitDate = isPeriod.value
        ? ["на", formatDate(valueDate.value[0], "ukr"), "-", formatDate(valueDate.value[1], "ukr")].join(" ")
        : formatDate(new Date(), "ukr");

      ElMessage.success(`Аналітика по руху коштів на ${limitDate} оновлена`);
    }
  } catch (e) {
    ElMessage.error("Помилка завантаження аналітики");
  }
};
</script>

<style>
.expand-contentBig {
  padding: 10px;
  background: #4caf5045;
  border-radius: 25px;
  margin: 0px 5px;
}
</style>
