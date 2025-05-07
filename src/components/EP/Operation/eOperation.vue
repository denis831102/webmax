<template>
  <eDialog_Operation
    v-model:visible="setting.dialog['editOperation'].visible"
    :namePunkt="activeName"
    :idPunkt="activeIdPunkt"
  />

  <el-tabs v-model="activeName" type="border-card" class="demo-tabs">
    <el-tab-pane v-for="punkt in punkts" :key="punkt.id" :name="punkt.name">
      <template #label>
        <span class="custom-tabs-label">
          <el-icon><calendar /></el-icon>
          <span>{{ punkt.name }}</span>
        </span>
      </template>

      <el-space :size="10" style="margin: -15px 0 10px 0">
        <el-card>
          <el-space :size="10">
            <el-switch v-model="isPeriod" @change="getTransaction" />
            <el-date-picker
              v-model="valueDate"
              type="daterange"
              format="DD.MM.YYYY"
              :start-placeholder="getDate"
              :end-placeholder="getDate"
              :disabled="!isPeriod"
              @change="getTransaction"
              style="width: 230px"
            />
          </el-space>
        </el-card>

        <el-card>
          <el-space :size="10">
            <el-input
              v-model="search"
              size="large"
              style="width: 100%"
              placeholder="Пошук по коментарю"
              @input="debouncedChange"
              :prefix-icon="Search"
            />
            <el-button-group class="ml-4">
              <el-button
                type="primary"
                :icon="HomeFilled"
                @click="newOperation()"
                >Нова операція
              </el-button>
              <el-button
                type="primary"
                plain
                :icon="Refresh"
                @click="getTransaction()"
              >
                Оновити
              </el-button>
            </el-button-group>
          </el-space>
        </el-card>
      </el-space>

      <el-table :data="filterTable">
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
                <el-table-column label="категорія" prop="name_K" />
                <el-table-column label="номенклатура" prop="name_M" />
                <el-table-column label="кількість" prop="count">
                  <template #default="props">
                    {{ props.row.count }}
                  </template>
                </el-table-column>
                <el-table-column label="ціна одиниці">
                  <template #default="props">
                    {{ props.row.price }}
                  </template>
                </el-table-column>
                <el-table-column label="сума" prop="suma" />
              </el-table>
            </div>
          </template>
        </el-table-column>

        <el-table-column type="index" />

        <el-table-column label="Дата" prop="date">
          <template #default="scope">
            <div style="display: flex; align-items: center">
              <el-icon><Calendar /></el-icon>
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
              <span style="margin-left: 10px">{{ scope.row.suma }} грн.</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Дії">
          <template #default="scope">
            <el-button
              size="small"
              @click="editTransaction(scope.$index, scope.row)"
              title="Редагування транзакції"
            >
              <el-icon><Edit /></el-icon>
            </el-button>

            <el-button
              size="small"
              type="danger"
              @click="delTransaction(scope.$index, scope.row)"
              title="Видалення транзакції"
            >
              <el-icon><DeleteFilled /></el-icon>
            </el-button>
            <el-button
              size="small"
              @click="copyTransaction(scope.$index, scope.row)"
              title="Створення транзакції за копією"
            >
              <el-icon><CopyDocument /></el-icon>
            </el-button>
          </template>
        </el-table-column>
      </el-table>

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
  onActivated,
  onUpdated,
  reactive,
  onUnmounted,
} from "vue";
import { useStore } from "vuex";
import { Search, Calendar } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import eDialog_Operation from "@/components/EP/Operation/eDialog_Operation";
import { HTTP } from "@/hooks/http";
import * as _ from "lodash";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
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

const newOperation = () => {
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

    if (response.data.isSuccesfull) {
      const _tab = setting.value.tables["tabTransaction"];
      _tab.data = _tab.data.filter((el) => el.id_T !== row.id_T);
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

const getPunktCur = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getPunktCur",
        _id_U: getCurUser.value.id,
      },
    });

    punkts.value = response.data;
    ElMessage.success("Пункти поточного користувача оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження пунктів");
  }
};

const getTransaction = async () => {
  try {
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

    ElMessage.success(
      response.data.total > 0
        ? `Транзакції для ${getCurUser.value.PIB.toUpperCase()} оновлені`
        : `Транзакції для ${getCurUser.value.PIB.toUpperCase()} відсутні`
    );
  } catch (e) {
    ElMessage.error("Помилка завантаження транзакцій");
  }
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
  activeName.value = punkts.value[0]["name"];
  await getTransaction();

  debouncedChange.value = _.debounce(() => {
    getTransaction();
  }, 1000);
});

onUpdated(async () => {
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
</style>
