<template>
  <eDialog_Operation
    v-model:visible="setting.dialog['editOperation'].visible"
    :namePunkt="activeName"
    :idPunkt="activeIdPunkt"
  />

  <el-tabs v-model="activeName" type="border-card" class="demo-tabs">
    <el-tab-pane v-for="punkt in punkts" :key="punkt.id" :name="punkt.name">
      <template #label>
        <span class="custom-tabs-label">
          <el-icon><calendar /></el-icon>
          <span>{{ punkt.name }}</span>
        </span>
      </template>

      <el-row :gutter="20" style="margin: 0 0 20px 10px">
        <el-col :span="6">
          <el-input
            v-model="search"
            size="small"
            style="width: 100%; height: 100%"
            placeholder="Пошук..."
            :prefix-icon="Search"
          />
        </el-col>
        <el-col :span="10">
          <el-button-group class="ml-4">
            <el-button type="primary" :icon="HomeFilled" @click="newOperation()"
              >Нова операція
            </el-button>
            <el-button type="primary" plain :icon="Refresh" @click="getBits()">
              Оновити
            </el-button>
          </el-button-group>
        </el-col>
      </el-row>
    </el-tab-pane>
  </el-tabs>
</template>

<script setup>
import { inject, ref, computed, onActivated } from "vue";
import { useStore } from "vuex";
import { Search, Calendar } from "@element-plus/icons-vue";
import { ElMessage } from "element-plus";
import eDialog_Operation from "@/components/EP/Bits/eDialog_Operation";
import { HTTP } from "@/hooks/http";

const setting = inject("setting");
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const search = ref("");
const punkts = ref([]);
const activeName = ref("");

const newOperation = () => {
  setting.value.dialog["editOperation"].initiator = "createOperation";
  setting.value.dialog["editOperation"].visible = true;
};

const getBits = () => {
  ElMessage.success("Буде оновлення...");
};

const activeIdPunkt = computed(() => {
  const punkt = punkts.value.find((el) => el.name == activeName.value);
  return punkts.value.length && punkt ? punkt.id : 0;
});

const getPunktCur = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getPunktCur",
        _id_U: getCurUser.value.id,
      },
    });

    punkts.value = response.data;
    ElMessage.success("Пункти поточного користувача оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження пунктів");
  }
};

onActivated(async () => {
  await getPunktCur();
  activeName.value = punkts.value[0]["name"];
});
</script>

<style>
.demo-tabs > .el-tabs__content {
  padding: 32px;
  color: #6b778c;
  font-size: 32px;
  font-weight: 600;
}
.demo-tabs .custom-tabs-label .el-icon {
  vertical-align: middle;
}
.demo-tabs .custom-tabs-label span {
  vertical-align: middle;
  margin-left: 4px;
}
</style>
