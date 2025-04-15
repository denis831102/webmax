<template>
  <div class="toolbar">
    <el-dropdown @command="handleCommand">
      <span class="el-dropdown-link">
        <el-icon style="margin-right: 8px; margin-top: 1px">
          <setting />
        </el-icon>
        {{ props.user.PIB }}
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
import { defineProps, inject } from "vue";
import { useRouter } from "vue-router";
import { ElMessage } from "element-plus";

const props = defineProps({
  user: Object,
});
const router = useRouter();
const setting = inject("setting");

const handleCommand = (command) => {
  switch (command) {
    case "data":
      ElMessage(`Прізвище: ${props.user.PIB}, логін: ${props.user.login}`);
      break;
    case "change":
      setting.value.dialog["edit"].visible = true;
      setting.value.dialog["edit"].initiator = "drop_user";
      break;
    case "exit":
      router.push({ name: "authent" });
      break;
    default:
      ElMessage(`команда: ${command}`);
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
