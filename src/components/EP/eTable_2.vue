<template>
  <el-table :data="setting.tableData" style="width: 100%">
    <el-table-column label="Дата" width="180">
      <template #default="scope">
        <div style="display: flex; align-items: center">
          <el-icon><timer /></el-icon>
          <span style="margin-left: 10px">{{ scope.row.date }}</span>
        </div>
      </template>
    </el-table-column>

    <el-table-column label="Ім'я" width="180">
      <template #default="scope">
        <el-popover effect="light" trigger="hover" placement="top" width="auto">
          <template #default>
            <div>ім'я: {{ scope.row.name }}</div>
            <div>адреса: {{ scope.row.address }}</div>
          </template>
          <template #reference>
            <el-tag>{{ scope.row.name }}</el-tag>
          </template>
        </el-popover>
      </template>
    </el-table-column>

    <el-table-column label="Операція">
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
import { inject } from "vue";
import { ElMessage } from "element-plus";
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
  setting.value.numRec = index;
  setting.value.dialog.edit.visible = true;
};
const handleDelete = (index, row) => {
  ElMessage(`del - index: ${index}, row: ${row}`);
  setting.value.tableData = setting.value.tableData.filter(
    (el, i) => i !== index
  );
};
</script>
