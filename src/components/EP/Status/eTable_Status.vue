<template>
  <el-row :gutter="20" style="margin: 0 0 20px 10px">
    <el-col :span="6">
      <el-input
        v-model="search"
        size="small"
        style="width: 100%; height: 100%"
        placeholder="Пошук статусу"
        :prefix-icon="Search"
      />
    </el-col>
    <el-col :span="10">
      <el-button-group class="ml-4">
        <el-button type="primary" :icon="Avatar" @click="addStatus()"
          >Додати новий статус</el-button
        >
        <el-button type="primary" :icon="Refresh" @click="getStatuses()">
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
    preserve-expanded-content="false"
    style="width: 100%"
  >
    <el-table-column type="expand">
      <template #default="props">
        <div style="padding: 20px; background: #c6e2ff69">
          <h3 style="margin: 0px 0 10px 0">
            Користувачі з статусом "{{ props.row.nameStatus }}"
          </h3>
          <el-table
            :data="props.row.listUser"
            border="true"
            style="background: #c6e2ff"
          >
            <el-table-column label="прізвище" prop="pib" />
            <el-table-column label="дата" prop="date" />
            <el-table-column label="час" prop="time" />
          </el-table>
        </div>
      </template>
    </el-table-column>

    <el-table-column type="index" width="30" />

    <el-table-column label="Статус" sortable prop="nameStatus">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><SetUp /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.nameStatus }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="доступ до користувачів">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-switch
            :modelValue="scope.row.v1 == 1"
            @change="changeCheck(scope.row, 1)"
          />
        </div>
      </template>
    </el-table-column>

    <el-table-column label="доступ до статусів">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-switch
            :modelValue="scope.row.v2 == 1"
            @change="changeCheck(scope.row, 2)"
          />
        </div>
      </template>
    </el-table-column>

    <el-table-column label="доступ 3">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-switch
            :modelValue="scope.row.v3 == 1"
            @change="changeCheck(scope.row, 3)"
          />
        </div>
      </template>
    </el-table-column>

    <el-table-column label="доступ 4">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-switch
            :modelValue="scope.row.v4 == 1"
            @change="changeCheck(scope.row, 4)"
          />
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Операція">
      <template #default="scope">
        <el-button size="small" @click="editStatus(scope.$index, scope.row)">
          <el-icon><Edit /></el-icon>
        </el-button>

        <el-button
          size="small"
          type="danger"
          @click="deleteStatus(scope.$index, scope.row)"
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
import { ElMessage } from "element-plus";
import { Search, Avatar, Refresh } from "@element-plus/icons-vue";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const search = ref("");

const editStatus = (index, row) => {
  setting.value.tables["tabStatus"].curRow = row;
  setting.value.dialog["editStatus"].visible = true;
};

const deleteStatus = async (index, row) => {
  const response = await HTTP.get("", {
    params: {
      _method: "delUser",
      _id: row["id"],
    },
  });

  if (response.data.isSuccesfull) {
    const _tab = setting.value.tables["tabUser"];
    _tab.data = _tab.data.filter((el, i) => i !== index);
  }
};

const getStatuses = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getStatuses",
      },
    });

    setting.value.tables["tabStatus"].data = response.data;
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addStatus = () => {
  setting.value.dialog["edit"].initiator = "table_user_add";
  setting.value.dialog["edit"].visible = true;
};

const sortMethod = () => {
  return (a, b) => {
    a > b ? 1 : -1;
  };
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabStatus"];

  return _tabl.data.filter((row) =>
    row.nameStatus.toLowerCase().includes(search.value.toLowerCase())
  );
});

const changeCheck = async (row, atr) => {
  const _tabl = setting.value.tables["tabStatus"];
  const curObj = _tabl.data.find((el) => el.id == row.id);
  const curVal = +curObj[`v${atr}`];

  const response = await HTTP.get("", {
    params: {
      _method: "setStatus",
      _id: row.id,
      _atr: `v${atr}`,
      _val: curVal ? 0 : 1,
    },
  });

  if (response.data.isSuccesfull) {
    curObj[`v${atr}`] = !curVal;
  } else {
    ElMessage("Помилка зміни");
  }
};

onActivated(getStatuses);
</script>
