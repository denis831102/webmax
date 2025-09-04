<template>
  <div>Заблоковано</div>
  <el-button @click="checkAccess">Перевірити</el-button>
</template>

<script setup>
import { useRouter } from "vue-router";
import { ElMessage } from "element-plus";
import { HTTP } from "@/hooks/http";

const router = useRouter();

const checkAccess = async () => {
  try {
    const response = await HTTP.post("", {
      _method: "checkAuthenticated",
    });

    if (response.data.answer.status) {
      router.push({ name: "authent" });
    } else {
      ElMessage({
        type: "error",
        dangerouslyUseHTMLString: true,
        message: response.data.answer.message,
      });
    }
  } catch (e) {
    ElMessage({
      type: "error",
      dangerouslyUseHTMLString: true,
      message: "Помилка авторизації",
    });
  }
};
</script>

<style scoped></style>
