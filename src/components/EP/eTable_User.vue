<template>
  <el-table :data="setting.tables['tabUser'].data" style="width: 100%">
    <el-table-column type="index" width="30" />

    <el-table-column label="Прізвище" width="180">
      <template #default="scope">
        <el-popover effect="light" trigger="hover" placement="top" width="auto">
          <template #default>
            <div>ім'я: {{ scope.row.PIB }}</div>
            <div>статус: {{ scope.row.rule }}</div>
          </template>
          <template #reference>
            <el-tag>{{ scope.row.PIB }}</el-tag>
          </template>
        </el-popover>
      </template>
    </el-table-column>

    <el-table-column label="Дані" width="180">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><Avatar /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.password }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="логін" width="100">
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

    <el-table-column label="Дата активності" width="140">
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
import { inject, onActivated } from "vue";
import { ElMessage } from "element-plus";
import axios from "axios";
//import { Timer } from "@element-plus/icons-vue";

const setting = inject("setting");

const handleEdit = (index, row) => {
  let sRow;
  for (let key in row) {
    sRow += ` ${key}: ${row[key]} `;
  }
  ElMessage({
    message: `edit - index: ${index}, row: ${sRow}`,
    type: "success",
    center: true,
  });

  setting.value.tables["tabUser"].numRec = index;
  setting.value.dialog["edit"].visible = true;
};

const handleDelete = (index, row) => {
  let _tab = setting.value.tables["tabUser"];

  _tab.data = _tab.data.filter((el, i) => i !== index);

  ElMessage(`del - index: ${index}, row: ${row}`);
};

const fetchUsers = async () => {
  try {
    const response = await axios.get(setting.value.server, {
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

onActivated(fetchUsers);
</script>
