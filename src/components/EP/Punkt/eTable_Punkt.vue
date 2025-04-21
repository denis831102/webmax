<template>
  <eDialog_Punkt v-model:visible="setting.dialog['editPunkt'].visible" />

  <el-row :gutter="20" style="margin: 0 0 20px 10px">
    <el-col :span="6">
      <el-input
        v-model="search"
        size="small"
        style="width: 100%; height: 100%"
        placeholder="Пошук за прізвищем"
        :prefix-icon="Search"
      />
    </el-col>
    <el-col :span="10">
      <el-button-group class="ml-4">
        <el-button type="primary" :icon="Avatar" @click="addPunkt()"
          >Додати нового</el-button
        >
        <el-button type="primary" plain :icon="Refresh" @click="getPunkts()">
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
    <el-table-column type="index" width="30" />

    <el-table-column label="Прізвище" sortable prop="PIB">
      <template #default="scope">
        <el-popover effect="light" trigger="hover" placement="top" width="auto">
          <template #default>
            <div>користувач: {{ scope.row.PIB }}</div>
            <div>статус: {{ scope.row.nameStatus }}</div>
            <div>id: {{ scope.row.id }}</div>
          </template>
          <template #reference>
            <el-tag>{{ scope.row.PIB }}</el-tag>
          </template>
        </el-popover>
      </template>
    </el-table-column>

    <!-- <el-table-column label="id" sortable prop="id" /> -->

    <el-table-column label="логін" sortable prop="login">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Avatar /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.login }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="пароль">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Key /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.password }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Дата активності" sortable prop="date">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Calendar /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.date }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Час активності">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><timer /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.time }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Операція">
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
      </template>
    </el-table-column>
  </el-table>
</template>

<script setup>
/* eslint-disable */

import { inject, onActivated, computed, ref } from "vue";
import { useStore } from "vuex";
import { ElMessage } from "element-plus";
import { Search, Avatar, Refresh } from "@element-plus/icons-vue";
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
  if (row["id"] == getCurUser.value.id) {
    ElMessage.error("Видаляти поточного не можна");
    return;
  }

  const response = await HTTP.get("", {
    params: {
      _method: "delPunkt",
      _id: row["id"],
    },
  });

  if (response.data.isSuccesfull) {
    const _tab = setting.value.tables["tabPunkt"];
    _tab.data = _tab.data.filter((el) => el.id !== row.id);
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
    ElMessage.success("Користувачі оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addPunkt = () => {
  setting.value.dialog["editPunkt"].initiator = "table_Punkt_add";
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
    row.PIB.toLowerCase().includes(search.value.toLowerCase())
  );
});

onActivated(getPunkts);
</script>
