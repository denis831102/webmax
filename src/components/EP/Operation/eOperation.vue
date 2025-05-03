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
            <el-switch v-model="isDate" />
            <el-date-picker
              v-model="valueDate"
              type="daterange"
              format="DD.MM.YYYY"
              :start-placeholder="getDate"
              :end-placeholder="getDate"
              :disabled="!isDate"
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

        <el-table-column label="Сума" prop="suma"> </el-table-column>

        <el-table-column label="Редагування">
          <template #default="scope">
            <el-button
              size="small"
              @click="editTransaction(scope.$index, scope.row)"
            >
              <el-icon><Edit /></el-icon>
            </el-button>

            <el-button
              size="small"
              type="danger"
              @click="delTransaction(scope.$index, scope.row)"
            >
              <el-icon><DeleteFilled /></el-icon>
            </el-button>
            <el-button
              size="small"
              @click="copyTransaction(scope.$index, scope.row)"
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
import { inject, ref, computed, onActivated, onUpdated, reactive } from "vue";
import { useStore } from "vuex";
import { Search, Calendar } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import eDialog_Operation from "@/components/EP/Operation/eDialog_Operation";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const search = ref("");
const valueDate = ref([new Date(), new Date()]);
const punkts = ref([]);
const activeName = ref("");
const isDate = ref(false);
const curDate = ref(new Date());
const setPagination = reactive({
  currentPage: 1,
  sizePage: 5,
  total: 1,
});

const newOperation = () => {
  setting.value.dialog["editOperation"].initiator = "createOperation";
  setting.value.dialog["editOperation"].visible = true;
};

const editTransaction = () => {
  ElMessage.success("Редагування транзакції ");
};

const delTransaction = () => {
  ElMessageBox.confirm("Ви точно бажаєте видалити транзакцію?")
    .then(() => {
      ElMessage.success("Видалення транзакції ");
    })
    .catch(() => {
      // catch error
    });
};

const copyTransaction = () => {
  ElMessage.success("Копіювання транзакції ");
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabTransaction"];
  const arDateL = calcDate(valueDate.value[0]).split(".");
  const arDateR = calcDate(valueDate.value[1]).split(".");
  const leftDate = new Date(`${arDateL[2]}-${arDateL[1]}-${arDateL[0]}`);
  const rightDate = new Date(`${arDateR[2]}-${arDateR[1]}-${arDateR[0]}`);

  return _tabl.data.filter((row) => {
    const arDate = row.date.split(".");
    const tDate = new Date(`${arDate[2]}-${arDate[1]}-${arDate[0]}`);

    return isDate.value
      ? row.comment.toLowerCase().includes(search.value.toLowerCase()) &&
          tDate >= leftDate &&
          tDate <= rightDate
      : row.comment.toLowerCase().includes(search.value.toLowerCase());
  });
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
      },
    });

    setting.value.tables["tabTransaction"].data = response.data.ar_data;
    setPagination.total = response.data.total;

    ElMessage.success(
      response.data.total > 0 ? "Транзакції оновлені" : "Транзакції відсутні"
    );
  } catch (e) {
    ElMessage.error("Помилка завантаження транзакцій");
  }
};

const getDate = computed(() => {
  return calcDate(curDate.value);
});

const calcDate = (valDate) => {
  const date = {
    d: valDate.getDate(),
    m: valDate.getMonth() + 1,
    y: valDate.getFullYear(),
  };
  return [
    (date.d < 10 ? "0" : "") + date.d,
    (date.m < 10 ? "0" : "") + date.m,
    date.y,
  ].join(".");
};

onActivated(async () => {
  await getPunktCur();
  activeName.value = punkts.value[0]["name"];
});

onUpdated(async () => {
  await getTransaction();
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
