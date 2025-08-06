<template>
  <eDialog_Punkt v-model:visible="setting.dialog['editPunkt'].visible" />

  <el-row :gutter="20" style="margin: 0 0 20px 10px">
    <el-col :span="6">
      <el-input
        v-model="search"
        size="small"
        style="width: 100%; height: 100%"
        placeholder="Пошук за пунктом"
        :prefix-icon="Search"
      />
    </el-col>
    <el-col :span="10">
      <el-button-group class="ml-4">
        <el-button type="primary" :icon="HomeFilled" @click="addPunkt()"
          >Додати нового
        </el-button>
        <el-button type="primary" plain :icon="Refresh" @click="getPunkt()">
          Оновити
        </el-button>
      </el-button-group>
    </el-col>
  </el-row>

  <el-table
    :data="filterTable"
    :default-sort="{ prop: 'id', order: 'ascending' }"
    :sort-method="sortMethod"
    highlight-current-row
    table-layout="fixed"
    style="width: 100%"
  >
    <el-table-column type="index" />

    <el-table-column label="Назва Пункту" sortable prop="name">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><HomeFilled /></el-icon>
          <div style="margin-left: 10px">{{ scope.row.name }}</div>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Адреса">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Position /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.adres }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="ПІБ менеджера" sortable prop="pib">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.pib }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Тип пункту" sortable prop="typeP">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><MagicStick /></el-icon>
          <span style="margin-left: 10px">{{
            scope.row.typeP == "sz" ? "СЗ" : "ЦМ"
          }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Редагування ПП">
      <template #default="scope">
        <el-button size="small" @click="editPunkt(scope.$index, scope.row)">
          <el-icon><Edit /></el-icon>
        </el-button>

        <el-button
          size="small"
          type="danger"
          @click="deletePunkt(scope.$index, scope.row)"
        >
          <el-icon><DeleteFilled /></el-icon>
        </el-button>
        <el-button size="small" @click="copyPunkt(scope.$index, scope.row)">
          <el-icon><CopyDocument /></el-icon>
        </el-button>
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
/* eslint-disable */

import { inject, onActivated, computed, ref } from "vue";
import { useStore } from "vuex";
import { ElMessage } from "element-plus";
import {
  Search,
  HomeFilled,
  CopyDocument,
  Refresh,
} from "@element-plus/icons-vue";
import { HTTP } from "@/hooks/http";
import eDialog_Punkt from "@/components/EP/Punkt/eDialog_Punkt";

const setting = inject("setting");
const search = ref("");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);

const editPunkt = (index, row) => {
  setting.value.tables["tabPunkt"].curRow = row;
  setting.value.dialog["editPunkt"].initiator = "table_Punkt_edit";
  setting.value.dialog["editPunkt"].visible = true;
};

const deletePunkt = async (index, row) => {
  const response = await HTTP.get("", {
    params: {
      _method: "delPunkt",
      _id: row["id"],
    },
  });

  if (response.data.isSuccesfull) {
    const _tab = setting.value.tables["tabPunkt"];
    _tab.data = _tab.data.filter((el) => el.id !== row.id);
  } else {
    ElMessage.error("Заборонено видаляти пункт з залишками на складі");
  }
};

const getPunkt = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getPunkt",
      },
    });

    setting.value.tables["tabPunkt"].data = response.data;
    ElMessage.success("Пункти оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addPunkt = () => {
  setting.value.dialog["editPunkt"].initiator = "table_Punkt_add";
  setting.value.dialog["editPunkt"].visible = true;
};
const copyPunkt = (index, row) => {
  setting.value.tables["tabPunkt"].curRow = row;
  setting.value.dialog["editPunkt"].initiator = "table_Punkt_copy_add";
  setting.value.dialog["editPunkt"].visible = true;
};

const sortMethod = () => {
  return (a, b) => {
    a > b ? 1 : -1;
  };
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabPunkt"];

  return _tabl.data.filter((row) =>
    row.name.toLowerCase().includes(search.value.toLowerCase())
  );
});

onActivated(getPunkt);
</script>
