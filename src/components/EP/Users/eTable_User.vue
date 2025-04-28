<template>
  <eDialog_User v-model:visible="setting.dialog['editUser'].visible" />

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
        <el-button type="primary" :icon="Avatar" @click="addUser()"
          >Додати нового</el-button
        >
        <el-button type="primary" plain :icon="Refresh" @click="getUsers()">
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
    preserve-expanded-content="false"
    style="width: 100%"
  >
    <el-table-column type="index" />

    <el-table-column type="expand">
      <template #default="props">
        <div style="padding: 20px; background: #c6e2ff69">
          <h3 style="margin: 0px 0 10px 0">
            Список пунктів користувача
            <el-text tag="b">{{ props.row.PIB }}</el-text>
          </h3>
          <el-table
            :data="props.row.listPunkt"
            v-if="props.row.listPunkt.length"
            border="true"
            style="background: #c6e2ff"
          >
            <el-table-column label="Назва пункта" prop="namePunkt" />
            <el-table-column label="Адреса" prop="adres" />
          </el-table>
          <el-text v-else type="danger">Пункти відсутні</el-text>
        </div>
      </template>
    </el-table-column>

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
        <el-button size="small" @click="editUser(scope.$index, scope.row)">
          <el-icon><Edit /></el-icon>
        </el-button>

        <el-button
          size="small"
          type="danger"
          @click="deleteUser(scope.$index, scope.row)"
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
import eDialog_User from "@/components/EP/Users/eDialog_User";

const setting = inject("setting");
const search = ref("");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);

const editUser = (index, row) => {
  setting.value.tables["tabUser"].curRow = row;
  setting.value.dialog["editUser"].initiator = "table_user_edit";
  setting.value.dialog["editUser"].visible = true;
};

const deleteUser = async (index, row) => {
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
    _tab.data = _tab.data.filter((el) => el.id !== row.id);
  }
};

const getUsers = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getUsers",
      },
    });

    setting.value.tables["tabUser"].data = response.data;
    ElMessage.success("Користувачі оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const addUser = () => {
  setting.value.dialog["editUser"].initiator = "table_user_add";
  setting.value.dialog["editUser"].visible = true;
};

const sortMethod = () => {
  return (a, b) => {
    a > b ? 1 : -1;
  };
};

const filterTable = computed(() => {
  const _tabl = setting.value.tables["tabUser"];

  return _tabl.data.length
    ? _tabl.data.filter((row) =>
        row.PIB.toLowerCase().includes(search.value.toLowerCase())
      )
    : _tabl.data;
});

onActivated(getUsers);
</script>
