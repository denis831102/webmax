<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="closeWindow"
    :title="form.title"
    width="500"
  >
    <el-form :model="form">
      <el-form-item label="Стара назва" :label-width="formLabelWidth">
        <el-text tag="b">{{ form.oldStatus }}</el-text>
        <el-button class="mb-4" type="primary" :icon="Edit" circle />
      </el-form-item>

      <el-form-item label="Нова назва" :label-width="formLabelWidth">
        <el-input v-model="form.newStatus" autocomplete="off" />
      </el-form-item>
    </el-form>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="$emit('update:visible', false)">Закрити</el-button>
        <el-button type="primary" @click="save"> Зберегти </el-button>
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
  onUpdated,
  computed,
} from "vue";
import { HTTP } from "@/hooks/http";
import { ElMessage } from "element-plus";
import { Edit } from "@element-plus/icons-vue";
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
});
const store = useStore();
const setCurUser = (user) => store.commit("setCurUser", user);
const getCurUser = computed(() => store.getters.getCurUser);

const closeWindow = () => {
  emit("update:visible", false);
};

const save = async () => {
  const _tab = setting.value.tables["tabUser"];
  const idStatus = setting.value.tables["tabStatus"].data.find(
    (el) => el.nameStatus == form.nameStatus
  ).id;

  const response = await HTTP.get("", {
    params: {
      _method: "changeDateUser",
      _id: _tab.curRow.id,
      _pib: form.pib,
      _login: form.login,
      _password: form.password,
      _idStatus: idStatus,
    },
  });

  if (response.data.isSuccesfull) {
    const user = _tab.data.find((el) => el.id == _tab.curRow.id);
    [user.PIB, user.idStatus, user.nameStatus, user.login, user.password] = [
      form.pib,
      idStatus,
      form.nameStatus,
      form.login,
      form.password,
    ];

    if (_tab.curRow.id == getCurUser.value.id) {
      setCurUser({
        id: getCurUser.value.id,
        idStatus,
        nameStatus: form.nameStatus,
        PIB: form.pib,
        login: form.login,
        password: form.password,
      });
      ElMessage.success("Дані поточного користувача змінені");
    }
    emit("update:visible", false);
  } else {
    ElMessage.error("Дані не змінені");
  }
};

onUpdated(async () => {
  form.title = "Редагування статуса";
  form.disabledStatus = false;

  const _tab = setting.value.tables["tabStatus"];

  [form.oldStatus] = [_tab.curRow.nameStatus];
});
</script>
