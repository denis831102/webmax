<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="closeWindow"
    :title="form.title"
    width="500"
  >
    <el-form :model="form">
      <el-form-item
        label="Стара назва"
        :label-width="formLabelWidth"
        v-if="form.disabledOldStatus"
      >
        <el-text tag="b">{{ form.oldStatus }}</el-text>
        <el-button
          style="margin-left: 10px"
          type="primary"
          :icon="Download"
          circle
          @click="clkDownload"
        />
      </el-form-item>

      <el-form-item label="Назва статуса" :label-width="formLabelWidth">
        <el-input v-model="form.newStatus" autocomplete="off" />
      </el-form-item>
    </el-form>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="closeWindow">Закрити</el-button>
        <el-button
          type="primary"
          @click="() => (form.disabledOldStatus ? clkChangeStatus() : clkAdd())"
        >
          Зберегти
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import {
  defineProps,
  reactive,
  defineEmits,
  inject,
  computed,
  onUpdated,
} from "vue";

import { HTTP } from "@/hooks/http";
import { ElMessage } from "element-plus";
import { Download } from "@element-plus/icons-vue";
import { useStore } from "vuex";

const props = defineProps({
  visible: Boolean,
});
const emit = defineEmits([]);
const setting = inject("setting");
const formLabelWidth = "140px";
const form = reactive({
  title: "",
  newStatus: "",
  oldStatus: "",
  disabledOldStatus: true,
});
const store = useStore();
const setCurUser = (user) => store.commit("setCurUser", user);
const getCurUser = computed(() => store.getters.getCurUser);

const closeWindow = () => {
  emit("update:visible", false);
  form.newStatus = "";
};

const clkChangeStatus = async () => {
  if (form.newStatus == form.oldStatus || !form.newStatus.length) {
    ElMessage.error({
      dangerouslyUseHTMLString: true,
      message: "Назва статуса не змінена. <BR> Введіть коректні дані",
    });
    return;
  }

  const _tab = setting.value.tables["tabStatus"];
  const status = _tab.data.find((el) => el.nameStatus == form.oldStatus);
  const response = await HTTP.get("", {
    params: {
      _method: "setStatus",
      _name: form.newStatus,
      _id: status.id,
    },
  });

  if (response.data.isSuccesfull) {
    status.nameStatus = form.newStatus;

    if (getCurUser.value.idStatus == status.id) {
      setCurUser({
        id: getCurUser.value.id,
        idStatus: status.id,
        nameStatus: form.newStatus,
        PIB: getCurUser.value.PIB,
      });
      ElMessage.success("Дані поточного користувача змінені");
    }
    emit("update:visible", false);
    form.newStatus = "";
  } else {
    ElMessage.error("Дані не змінені");
  }
};

const clkDownload = () => {
  form.newStatus = form.oldStatus;
};

const clkAdd = async () => {
  const response = await HTTP.get("", {
    params: {
      _method: "addStatus",
      _name: form.newStatus,
    },
  });

  if (response.data.isSuccesfull) {
    const _tab = setting.value.tables["tabStatus"];
    const status = {
      id: response.data.id,
      nameStatus: response.data.name,
    };
    _tab.data.push(status);

    ElMessage.success(response.data.message);
    emit("update:visible", false);
  } else {
    ElMessage.error(response.data.message);
  }
};

onUpdated(async () => {
  switch (setting.value.dialog["editStatus"].initiator) {
    case "edit": {
      form.title = "Редагування статуса";
      const _tab = setting.value.tables["tabStatus"];
      form.oldStatus = _tab.curRow.nameStatus;
      form.disabledOldStatus = true;
      break;
    }
    case "add": {
      form.title = "Створення статуса";
      form.disabledOldStatus = false;
      form.newStatus = "";
      break;
    }
  }
});
</script>
