<template>
  <eDialog_Material v-model:visible="setting.dialog['editMaterial'].visible" />

  <el-row :gutter="20" style="margin: 0 0 20px 10px">
    <el-col :span="6">
      <el-input
        v-model="searchKat"
        size="small"
        style="width: 100%; height: 100%"
        placeholder="Пошук категорії"
        :prefix-icon="Search"
      />
    </el-col>
    <el-col :span="6">
      <el-input
        v-model="search"
        size="small"
        style="width: 100%; height: 100%"
        placeholder="Пошук матеріла"
        :prefix-icon="Search"
      />
    </el-col>
    <el-col :span="10">
      <el-button-group class="ml-4">
        <el-button type="primary" :icon="HomeFilled" @click="addMaterial()"
          >Додати нового
        </el-button>
        <el-button type="primary" plain :icon="Refresh" @click="getMaterial()">
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

    <el-table-column label="Категорія" sortable prop="name_K">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><CollectionTag /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.name_K }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Номенклатура" sortable prop="name_M">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><HomeFilled /></el-icon>
          <div style="margin-left: 10px">{{ scope.row.name_M }}</div>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Одиниця виміру" sortable prop="unit">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><User /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.unit }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label=" Редагування ">
      <template #default="scope">
        <el-button size="small" @click="editMaterial(scope.$index, scope.row)">
          <el-icon><Edit /></el-icon>
        </el-button>

        <el-button
          size="small"
          type="danger"
          @click="deleteMaterial(scope.$index, scope.row)"
        >
          <el-icon><DeleteFilled /></el-icon>
        </el-button>
        <el-button size="small" @click="copyMaterial(scope.$index, scope.row)">
          <el-icon><CopyDocument /></el-icon>
        </el-button>
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
/* eslint-disable */

import { inject, onActivated, computed, ref } from "vue";
import { ElMessage } from "element-plus";
import {
  Search,
  HomeFilled,
  CopyDocument,
  Refresh,
  CollectionTag,
} from "@element-plus/icons-vue";
import { HTTP } from "@/hooks/http";
import eDialog_Material from "@/components/EP/Material/eDialog_Material";

const setting = inject("setting");
const search = ref("");
const searchKat = ref("");

const editMaterial = (index, row) => {
  setting.value.tables["tabMaterial"].curRow = row;
  setting.value.dialog["editMaterial"].initiator = "table_Material_edit";
  setting.value.dialog["editMaterial"].visible = true;
};

const deleteMaterial = async (index, row) => {
  const response = await HTTP.get("", {
    params: {
      _method: "delMaterial",
      _id_M: row["id_M"],
    },
  });

  if (response.data.isSuccesfull) {
    const _tab = setting.value.tables["tabMaterial"];
    _tab.data = _tab.data.filter((el) => el.id_M !== row.id_M);
  } else {
    ElMessage.error("Заборонено видаляти номенклатуру з залишками на складі");
  }
};

const getMaterial = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getMaterial",
      },
    });
    setting.value.tables["tabMaterial"].data = response.data;
    ElMessage.success("Матеріали оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addMaterial = () => {
  setting.value.dialog["editMaterial"].initiator = "table_Material_add";
  setting.value.dialog["editMaterial"].visible = true;
};
const copyMaterial = (index, row) => {
  setting.value.tables["tabMaterial"].curRow = row;
  setting.value.dialog["editMaterial"].initiator = "table_Material_copy_add";
  setting.value.dialog["editMaterial"].visible = true;
};

const sortMethod = () => {
  return (a, b) => {
    a > b ? 1 : -1;
  };
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabMaterial"];

  return _tabl.data.filter((row) => {
    return (
      row.name_M.toLowerCase().includes(search.value.toLowerCase()) &&
      row.name_K.toLowerCase().includes(searchKat.value.toLowerCase())
    );
  });
});

onActivated(() => {
  getMaterial();
});
</script>
