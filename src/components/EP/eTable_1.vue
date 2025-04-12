<template>
  <el-scrollbar>
    <el-table :data="tableData" height="450" style="width: 100%">
      <el-table-column prop="date" label="Дата" width="140" />
      <el-table-column prop="name" label="Ім'я" width="120" />
      <el-table-column prop="address" label="Адреса" />
    </el-table>
  </el-scrollbar>
</template>

<script setup>
import { ref } from "vue";

const item = {
  date: "2016-05-02",
  name: "Tom",
  address: "No. 189, Grove St, Los Angeles",
};
const tableData = ref(Array.from({ length: 20 }).fill(item));
</script>
