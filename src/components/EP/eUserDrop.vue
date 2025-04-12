<template>
  <div class="toolbar">
    <el-dropdown @command="handleCommand">
      <span class="el-dropdown-link">
        <el-icon style="margin-right: 8px; margin-top: 1px">
          <setting />
        </el-icon>
        {{ props.name }}
      </span>
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item command="data">Дані</el-dropdown-item>
          <el-dropdown-item command="change">Змінити</el-dropdown-item>
          <el-dropdown-item command="exit">Вихід</el-dropdown-item>
        </el-dropdown-menu>
      </template>
    </el-dropdown>
  </div>
</template>

<script setup>
import { defineProps } from "vue";
import { useRouter } from "vue-router";
import { ElMessage } from "element-plus";

const props = defineProps({
  name: String,
});

const router = useRouter();

const handleCommand = (command) => {
  ElMessage(`команда: ${command}`);
  switch (command) {
    case "exit":
      router.push({ name: "profile" });
      break;
  }
};
</script>

<style scoped>
.el-dropdown-link {
  cursor: pointer;
  color: var(--el-color-primary);
  display: flex;
  align-items: center;
}
</style>
