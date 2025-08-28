<template>
  <eDialog_User v-model:visible="setting.dialog['editUser'].visible" />

  <eDialog_Maneger v-model:visible="setting.dialog['editManeger'].visible" />

  <div class="toolbar">
    <el-dropdown @command="handleCommand">
      <span class="el-dropdown-link">
        <el-icon style="margin-right: 8px; margin-top: 1px"><User /></el-icon>
        {{ props.user.PIB }}
      </span>
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item command="data">
            <el-icon><User /></el-icon>
            Дані користувача
          </el-dropdown-item>
          <el-dropdown-item command="redactor">
            <el-icon><Edit /></el-icon>
            Редагувати дані
          </el-dropdown-item>
          <el-dropdown-item command="change" v-if="+getCurUser.listAccess[8]">
            <el-icon><Platform /></el-icon>
            Обрати менеджера
          </el-dropdown-item>
          <el-dropdown-item command="exit">
            <el-icon><CloseBold /></el-icon>
            Вихід
          </el-dropdown-item>
        </el-dropdown-menu>
      </template>
    </el-dropdown>
  </div>
</template>

<script setup>
import { defineProps, inject, computed } from "vue";
import eDialog_User from "@/components/EP/Users/eDialog_User";
import eDialog_Maneger from "@/components/EP/Users/eDialog_Maneger";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import { ElMessage } from "element-plus";

const props = defineProps({
  user: Object,
});
const router = useRouter();
const setting = inject("setting");

const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);

const handleCommand = (command) => {
  switch (command) {
    case "data":
      ElMessage({
        dangerouslyUseHTMLString: true,
        message: `<div style="color:black; line-height:1.5; font-size:11pt;">
            <b>прізвище:</b> ${props.user.PIB} <br> 
            <b>статус:</b> ${props.user.nameStatus} <br> 
            <b>логін:</b> ${props.user.login}</div>`,
      });
      break;
    case "redactor":
      setting.value.dialog["editUser"].visible = true;
      setting.value.dialog["editUser"].initiator = "drop_user";
      break;
    case "change":
      setting.value.dialog["editManeger"].visible = true;
      setting.value.dialog["editManeger"].initiator = "drop_user";
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
