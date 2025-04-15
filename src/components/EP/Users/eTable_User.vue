<template>
  <el-row :gutter="10" style="margin: 0 0 10px 10px">
    <el-col :span="6">
      <el-button type="primary" :icon="Avatar" @click="addUser()"
        >Додати нового</el-button
      >
    </el-col>
    <el-col :span="6">
      <el-input
        v-model="search"
        size="small"
        style="width: 100%; height: 100%"
        placeholder="Пошук за прізвищем"
        :prefix-icon="Search"
      />
    </el-col>
  </el-row>

  <el-table
    :data="filterTable"
    :default-sort="{ prop: 'id', order: 'ascending' }"
    :sort-method="sortMethod"
    highlight-current-row
    style="width: 100%"
  >
    <el-table-column type="index" width="30" />

    <el-table-column label="Прізвище" width="180" sortable prop="PIB">
      <template #default="scope">
        <el-popover effect="light" trigger="hover" placement="top" width="auto">
          <template #default>
            <div>користувач: {{ scope.row.PIB }}</div>
            <div>id: {{ scope.row.id }}</div>
            <div>статус: {{ scope.row.rule }}</div>
          </template>
          <template #reference>
            <el-tag>{{ scope.row.PIB }}</el-tag>
          </template>
        </el-popover>
      </template>
    </el-table-column>

    <el-table-column label="id" width="80" sortable prop="id" />

    <el-table-column label="логін" width="100" sortable prop="login">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Avatar /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.login }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="пароль" width="100">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Key /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.password }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Дата активності" width="160" sortable prop="date">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Calendar /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.date }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Час активності" width="140">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><timer /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.time }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Операція" width="180">
      <template #default="scope">
        <el-button size="small" @click="handleEdit(scope.$index, scope.row)">
          <el-icon><Edit /></el-icon>
        </el-button>

        <el-button
          size="small"
          type="danger"
          @click="handleDelete(scope.$index, scope.row)"
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
import { Search, Avatar } from "@element-plus/icons-vue";
import { HTTP } from "@/hooks/http";
//import { Timer } from "@element-plus/icons-vue";

const setting = inject("setting");

const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);

const handleEdit = (index) => {
  // let sRow = "";
  // for (let key in row) {
  //   sRow += ` ${key}: ${row[key]} `;
  // }
  // ElMessage({
  //   message: `edit - index: ${index}, row - ${sRow}`,
  //   type: "success",
  //   center: true,
  // });

  setting.value.tables["tabUser"].numRec = index;
  setting.value.dialog["edit"].initiator = "table_user_edit";
  setting.value.dialog["edit"].visible = true;
};

const handleDelete = async (index, row) => {
  if (row["id"] == getCurUser.value.id) {
    ElMessage.error("Видаляти поточного не можна");
    return;
  }

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

const fetchUsers = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _key: setting.value.key,
        _method: "getUsers",
      },
    });

    setting.value.tables["tabUser"].data = response.data;
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addUser = () => {
  setting.value.dialog["edit"].initiator = "table_user_add";
  setting.value.dialog["edit"].visible = true;
};

const sortMethod = () => {
  return (a, b) => {
    // a > b ? 1 : -1;
    a - b;
  };
};

const search = ref("");

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabUser"];

  return _tabl.data.filter((row) =>
    row.PIB.toLowerCase().includes(search.value.toLowerCase())
  );
});

onActivated(fetchUsers);
</script>
