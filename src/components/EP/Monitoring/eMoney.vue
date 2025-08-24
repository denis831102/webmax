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
        >
          <template #header>
            <el-checkbox v-model="checkAll" :indeterminate="indeterminate">
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

        <el-button type="primary" plain :icon="Refresh"> Оновити </el-button>
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
                  :data="scope.row.listVidKasa"
                  :default-sort="{ prop: 'date', order: 'ascending' }"
                  style="margin-left: 4%; background: #124578; width: 95%"
                  stripe
                  border
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
                        show-summary
                      >
                        <el-table-column>
                          <template #default="scope">
                            <div style="display: flex; align-items: center">
                              <span style="margin-left: 3%">{{
                                scope.row.nameN
                              }}</span>
                            </div>
                          </template>
                        </el-table-column>
                        <el-table-column label="Сума, грн " prop="suma" />
                      </el-table>
                    </template>
                  </el-table-column>

                  <el-table-column label="Рух коштів" prop="nameVidKassa" />

                  <el-table-column label="Сума, грн " />
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
/* eslint-disable */
//отключает наблюдатель за ошибками
import { inject, ref, onActivated, computed } from "vue";
import { useStore } from "vuex";
// import { User, Refresh, Tickets } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";
// import { loadFile } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const checkManeger = ref([]);
const listManeger = ref([]);
const valueDate = ref([new Date(), new Date()]);
const isPeriod = ref(true);

const filterTable = computed(() => {
  //   const _tabl = setting.value.tables["tabEconomist"];

  const _tabl = {
    data: [
      {
        id_U: "30",
        pib: "Ратова Євгенія",
        listPunkt: [
          {
            id: "11",
            namePunkt: "ДМС_ЦМ",
            listVidKasa: [
              {
                id: "4",
                nameVidKassa: "приход",
                listInKassa: [
                  {
                    id_N: "89",
                    nameN: "поповнення",
                    suma: "10000",
                  },
                ],
              },
              {
                id: "5",
                nameVidKassa: "расход",
                listInKassa: [
                  {
                    id_N: "45",
                    nameN: "закупівля матеріала",
                    suma: "8000",
                  },
                ],
              },
            ],
          },
          {
            id: "18",
            namePunkt: "Киев_Отрадный ",
            listVidKasa: [
              {
                id: "4",
                nameVidKassa: "приход",
                listInKassa: [
                  {
                    id_N: "891",
                    nameN: "поповнення",
                    suma: "5000",
                  },
                ],
              },
              {
                id: "5",
                nameVidKassa: "расход",
                listInKassa: [
                  {
                    id_N: "45",
                    nameN: "закупівля матеріала",
                    suma: "2000",
                  },
                ],
              },
            ],
          },
          {
            id: "29",
            namePunkt: "Черноморск",
            listVidKasa: [
              {
                id: "4",
                nameVidKassa: "приход",
                listInKassa: [
                  {
                    id_N: "89",
                    nameN: "поповнення",
                    suma: "3000",
                  },
                  {
                    id_N: "91",
                    nameN: "надходження за продаж",
                    suma: "500",
                  },
                ],
              },
              {
                id: "5",
                nameVidKassa: "расход",
                listInKassa: [
                  {
                    id_N: "45",
                    nameN: "закупівля матеріала",
                    suma: "2800",
                  },
                ],
              },
            ],
          },
        ],
      },
    ],
  };
  return _tabl.data;
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

onActivated(async () => {
  await getManeger();
});
</script>

<style></style>
