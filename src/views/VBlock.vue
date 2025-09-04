<template>
  <el-row>
    <el-col :span="8" />
    <el-col :span="8">
      <el-row>
        <el-col :span="8" />
        <el-col :span="8">
          <img
            style="text-align: center; margin: 70px 0 20px 0"
            src="https://webmax.lond.lg.ua/php/img/block.jpg"
          />
        </el-col>
        <el-col :span="8" />
      </el-row>
      <el-row
        ><el-col :span="24">
          <div style="text-align: center">
            По причині багаторазового вводу не вірних параметрів авторизації Вас
            заблоковано.
          </div>
        </el-col></el-row
      >
      <el-row>
        <el-col :span="10" />
        <el-col :span="4"
          ><el-button @click="checkAccess">Перевірити</el-button>
        </el-col>
      </el-row>
      <el-col :span="10" />
    </el-col>
    <el-col :span="8" />
  </el-row>
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

<style scoped>
img {
  width: 200px;
  height: 200px;
}
</style>
