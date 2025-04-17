<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="closeWindow"
    :title="form.title"
    width="500"
  >
    <el-form :model="form">
      <el-form-item label="Прізвище, ім'я" :label-width="formLabelWidth">
        <el-input v-model="form.pib" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Логін" :label-width="formLabelWidth">
        <el-input v-model="form.login" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Пароль" :label-width="formLabelWidth">
        <el-input v-model="form.password" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Статус" :label-width="formLabelWidth">
        <el-select v-model="form.nameStatus" :disabled="form.disabledStatus">
          <el-option
            v-for="item in sourceTable"
            :key="item.idStatus"
            :label="item.idStatus"
            :value="item.nameStatus"
          />
        </el-select>
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
import { useStore } from "vuex";

const props = defineProps({
  visible: Boolean,
});
const emit = defineEmits([]);
const setting = inject("setting");
const formLabelWidth = "140px";
const form = reactive({
  title: "",
  pib: "",
  nameStatus: "",
  login: "",
  password: "",
  disabledStatus: false,
});
const store = useStore();
const setCurUser = (user) => store.commit("setCurUser", user);
const getCurUser = computed(() => store.getters.getCurUser);

const closeWindow = () => {
  emit("update:visible", false);
};

const save = async () => {
  switch (setting.value.dialog["edit"].initiator) {
    case "table_user_edit": {
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
        [user.PIB, user.idStatus, user.nameStatus, user.login, user.password] =
          [form.pib, idStatus, form.nameStatus, form.login, form.password];

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
      break;
    }
    case "drop_user": {
      const _tab = setting.value.tables["tabUser"].data;
      const user = getCurUser.value;

      const response = await HTTP.get("", {
        params: {
          _method: "changeDateUser",
          _id: user.id,
          _pib: form.pib,
          _login: form.login,
          _password: form.password,
          _idStatus: user.idStatus,
        },
      });

      if (response.data.isSuccesfull) {
        setCurUser({
          id: user.id,
          PIB: form.pib,
          login: form.login,
          password: form.password,
          idStatus: user.idStatus,
          listAccess: response.data.listAccess,
        });

        if (_tab.length) {
          const curUser = _tab.find((el) => el.id);
          [curUser.PIB, curUser.login, curUser.password] = [
            form.pib,
            form.login,
            form.password,
          ];
        }

        ElMessage.success("Дані поточного користувача змінені");
        emit("update:visible", false);
      } else {
        ElMessage.error("Дані не змінені");
      }
      break;
    }
    case "table_user_add": {
      const idStatus = setting.value.tables["tabStatus"].data.find(
        (el) => el.nameStatus == form.nameStatus
      ).id;

      const response = await HTTP.get("", {
        params: {
          _method: "addUser",
          _pib: form.pib,
          _login: form.login,
          _password: form.password,
          _idStatus: idStatus,
          _nameStatus: form.nameStatus,
        },
      });

      if (response.data.isSuccesfull) {
        const _tab = setting.value.tables["tabUser"];

        _tab.data.push(response.data.user);
        ElMessage.success("Нового користувача додано");
        emit("update:visible", false);
      } else {
        ElMessage.error("Користувача не додано");
      }
      break;
    }
  }
};

const sourceTable = computed(() => {
  return setting.value.tables["tabStatus"].data;
});

onUpdated(async () => {
  switch (setting.value.dialog["edit"].initiator) {
    case "table_user_edit": {
      form.title = "Редагування користувача";
      form.disabledStatus = false;

      const _tab = setting.value.tables["tabUser"];

      const response = await HTTP.get("", {
        params: {
          _method: "getStatuses",
        },
      });
      setting.value.tables["tabStatus"].data = response.data;

      [form.pib, form.login, form.password, form.nameStatus] = [
        _tab.curRow.PIB,
        _tab.curRow.login,
        _tab.curRow.password,
        _tab.curRow.nameStatus,
      ];
      break;
    }
    case "drop_user": {
      form.title = "Редагування користувача";
      form.disabledStatus = true;

      const user = getCurUser.value;

      form.pib = user.PIB;
      form.login = user.login;
      form.password = user.password;
      form.nameStatus = user.nameStatus;

      if (!setting.value.tables["tabStatus"].data.length) {
        const response = await HTTP.get("", {
          params: {
            _method: "getStatuses",
          },
        });
        setting.value.tables["tabStatus"].data = response.data;
      }

      break;
    }
    case "table_user_add": {
      form.title = "Додавання користувача";
      form.disabledStatus = false;

      form.pib = "";
      form.login = "";
      form.password = "";
      form.nameStatus = setting.value.tables["tabStatus"].data[3].nameStatus;

      break;
    }
  }
});
</script>
