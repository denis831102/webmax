<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="handleClose"
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
        <el-select v-model="form.status" placeholder="Оберіть статус">
          <el-option label="Адміністратор" value="admin" />
          <el-option label="Менеджер" value="manager" />
          <el-option label="Торговий представник" value="manager" />
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
  status: "",
  login: "",
  password: "",
});
const store = useStore();
const setCurUser = (user) => store.commit("setCurUser", user);
const getCurUser = computed(() => store.getters.getCurUser);

const handleClose = () => {
  emit("update:visible", false);
};

const save = async () => {
  switch (setting.value.dialog["edit"].initiator) {
    case "table_user_edit": {
      const _tab = setting.value.tables["tabUser"];

      const response = await HTTP.get("", {
        params: {
          _method: "changeDateUser",
          _id: _tab.data[_tab.numRec].id,
          _pib: form.pib,
          _login: form.login,
          _password: form.password,
        },
      });

      if (response.data.isSuccesfull) {
        _tab.data[_tab.numRec].PIB = form.pib;
        _tab.data[_tab.numRec].login = form.login;
        _tab.data[_tab.numRec].password = form.password;

        if (_tab.data[_tab.numRec].id == getCurUser.value.id) {
          setCurUser({
            id: getCurUser.value.id,
            PIB: form.pib,
            login: form.login,
            password: form.password,
          });
          ElMessage.success("Дані поточного користувача змінені.");
        }
        emit("update:visible", false);
      } else {
        ElMessage.error("Дані не змінені.");
      }
      break;
    }
    case "drop_user": {
      const _tab = setting.value.tables["tabUser"];
      const user = getCurUser.value;

      const response = await HTTP.get("", {
        params: {
          _method: "changeDateUser",
          _id: user.id,
          _pib: form.pib,
          _login: form.login,
          _password: form.password,
        },
      });

      if (response.data.isSuccesfull) {
        setCurUser({
          id: user.id,
          PIB: form.pib,
          login: form.login,
          password: form.password,
        });

        _tab.data[_tab.numRec].PIB = form.pib;
        _tab.data[_tab.numRec].login = form.login;
        _tab.data[_tab.numRec].password = form.password;

        ElMessage.success("Дані поточного користувача змінені.");
        emit("update:visible", false);
      } else {
        ElMessage.error("Дані не змінені.");
      }
      break;
    }
    case "table_user_add": {
      const response = await HTTP.get("", {
        params: {
          _method: "addUser",
          _pib: form.pib,
          _login: form.login,
          _password: form.password,
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

onUpdated(() => {
  switch (setting.value.dialog["edit"].initiator) {
    case "table_user_edit": {
      form.title = "Редагування користувача";

      const _tab = setting.value.tables["tabUser"];

      form.pib = _tab.data[_tab.numRec].PIB;
      form.login = _tab.data[_tab.numRec].login;
      form.password = _tab.data[_tab.numRec].password;
      break;
    }
    case "drop_user": {
      form.title = "Редагування користувача";

      const user = getCurUser.value;

      form.pib = user.PIB;
      form.login = user.login;
      form.password = user.password;
      break;
    }
    case "table_user_add": {
      form.title = "Додавання користувача";

      form.pib = "";
      form.login = "";
      form.password = "";
      break;
    }
  }
});
</script>
