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

      <el-row :gutter="20" style="margin: 0 0 20px 10px">
        <el-col :span="6">
          <el-input
            v-model="search"
            size="small"
            style="width: 100%; height: 100%"
            placeholder="Пошук..."
            :prefix-icon="Search"
          />
        </el-col>
        <el-col :span="10">
          <el-button-group class="ml-4">
            <el-button type="primary" :icon="HomeFilled" @click="newOperation()"
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
        </el-col>
      </el-row>

      <el-table :data="filterTable">
        <el-table-column type="expand">
          <template #default="props">
            <div style="padding: 20px; background: #c6e2ff69">
              <h3 style="margin: 0px 0 10px 0">
                Транзакціїї по операціїї "{{ props.row.comment }}
                {{ props.row.date }} {{ props.row.time }}"
              </h3>
              <el-table
                :data="props.row.listOper"
                border="true"
                style="background: #c6e2ff"
                show-summary
              >
                <el-table-column label="категорія" prop="name_K" />
                <el-table-column label="номенклатура" prop="name_M" />
                <el-table-column label="кількість" prop="count" />
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
          <!-- <template #default="scope">
            <div style="display: flex; align-items: center">
              <el-icon><SetUp /></el-icon>
              <span style="margin-left: 10px">{{ scope.row.nameStatus }}</span>
            </div>
          </template> -->
        </el-table-column>

        <el-table-column label="Коментар" prop="comment"> </el-table-column>
      </el-table>
    </el-tab-pane>
  </el-tabs>
</template>

<script setup>
import { inject, ref, computed, onActivated, onUpdated } from "vue";
import { useStore } from "vuex";
import { Search, Calendar } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import eDialog_Operation from "@/components/EP/Bits/eDialog_Operation";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const search = ref("");
const punkts = ref([]);
const activeName = ref("");

const newOperation = () => {
  setting.value.dialog["editOperation"].initiator = "createOperation";
  setting.value.dialog["editOperation"].visible = true;
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabTransaction"];

  // return _tabl.data.filter((row) =>
  //   row.nameStatus.toLowerCase().includes(search.value.toLowerCase())
  //);
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
      },
    });

    setting.value.tables["tabTransaction"].data = response.data;
    ElMessage.success("Транзакції оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження транзакцій");
  }
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
