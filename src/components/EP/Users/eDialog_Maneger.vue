<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="closeWindow"
    :title="form.title"
    width="500"
    align-center
  >
    <el-form :model="form">
      <el-form-item label="Операції для" :label-width="formLabelWidth">
        <el-radio-group v-model="setting.dialog['editManeger'].chooseUser">
          <el-radio-button value="user" label="користувач" />
          <el-radio-button value="maneger" label="менеджер" />
        </el-radio-group>
      </el-form-item>
      <el-form-item label="Менеджер" :label-width="formLabelWidth">
        <el-select
          v-model="setting.dialog['editManeger'].idManeger"
          :disabled="setting.dialog['editManeger'].chooseUser != 'maneger'"
        >
          <el-option
            v-for="item in sourceTable"
            :key="item.id"
            :label="item.PIB"
            :value="item.id"
          />
        </el-select>
      </el-form-item>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="$emit('update:visible', false)">Закрити</el-button>
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
import { useStore } from "vuex";

const props = defineProps({
  visible: Boolean,
});
const emit = defineEmits([]);
const setting = inject("setting");
const formLabelWidth = "140px";
const form = reactive({
  title: "",
  //idManeger: "",
  // chooseUser: "",
});
const store = useStore();
// const setCurUser = (user) => store.commit("setCurUser", user);
const getCurUser = computed(() => store.getters.getCurUser);

const closeWindow = () => {
  emit("update:visible", false);
};

const sourceTable = computed(() => {
  return setting.value.tables["tabUser"].data;
});

onUpdated(async () => {
  switch (setting.value.dialog["editManeger"].initiator) {
    case "drop_user": {
      const user = getCurUser.value;

      form.title = "Перейти на операції менеджера";
      if (!setting.value.dialog["editManeger"].idManeger) {
        setting.value.dialog["editManeger"].idManeger = user.id;
      }
      //form.idManeger = user.id;
      // form.chooseUser = setting.value.dialog["editManeger"].chooseUser;

      // if (!setting.value.tables["tabUser"].data.length) {
      const response = await HTTP.get("", {
        params: {
          _method: "getUsers",
        },
      });

      setting.value.tables["tabUser"].data = response.data;
      // }
      break;
    }
  }
});
</script>
