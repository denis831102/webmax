<template>
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

    <el-col :span="10">
      <el-button-group class="ml-4">
        <el-button
          type="primary"
          plain
          :icon="Refresh"
          @click="getKategories()"
        >
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
    <el-table-column type="expand">
      <template #default="props">
        <div style="padding: 20px; background: #c6e2ff69">
          <h3 style="margin: 0px 0 10px 0">
            Матеріал категорії "{{ props.row.name_K }}"
          </h3>
          <el-table
            :data="props.row.listMaterial"
            border="true"
            style="background: #c6e2ff"
          >
            <el-table-column label="номенклатура" prop="name_M" />
            <el-table-column label="одиниця виміру" prop="unit" width="200" />
          </el-table>
        </div>
      </template>
    </el-table-column>
    <el-table-column type="index" />

    <el-table-column label="Категорія" sortable prop="name_K">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><CollectionTag /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.name_K }}</span>
        </div>
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
/* eslint-disable */

import { inject, onActivated, computed, ref } from "vue";
import { ElMessage } from "element-plus";
import { Search, Refresh, CollectionTag } from "@element-plus/icons-vue";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const searchKat = ref("");

const getKategories = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getKategories",
      },
    });
    setting.value.tables["tabKategories"].data = response.data;
    ElMessage.success("Категоріїї оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const sortMethod = () => {
  return (a, b) => {
    a > b ? 1 : -1;
  };
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabKategories"];

  return _tabl.data.filter((row) =>
    row.name_K.toLowerCase().includes(searchKat.value.toLowerCase())
  );
});

onActivated(() => {
  getKategories();
});
</script>
