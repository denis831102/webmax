<template>
  <el-dialog
    :modelValue="props.visible"
    title="Авторизація"
    width="400"
    :before-close="handleClose"
    @keydown="changeKey"
  >
    <el-form
      :model="form"
      label-width="auto"
      style="max-width: 600px; border: 0"
    >
      <el-form-item label="Логін">
        <el-input v-model="form.login" placeholder="введіть логін" />
      </el-form-item>
      <el-form-item label="Пароль">
        <el-input
          v-model="form.password"
          type="password"
          show-password="true"
          placeholder="введіть пароль"
          :autofocus="autoFoc"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button type="primary" @click="enter">Увійти</el-button>
        <el-button @click="close">Скасувати</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import {
  reactive,
  defineProps,
  defineEmits,
  computed,
  // onMounted,
  ref,
} from "vue";
import { ElMessage } from "element-plus";
import { useStore } from "vuex";
import { useRouter } from "vue-router";

const props = defineProps({
  visible: Boolean,
});

const emit = defineEmits([]);

const form = reactive({
  login: "",
  password: "",
});

const autoFoc = ref(false);

const store = useStore();
const checkAuthenticated = (val) => store.dispatch("checkAuthenticated", val);
const getAuthenticated = computed(() => store.getters.getAuthenticated);
const getAnswerServer = computed(() => store.getters.getAnswerServer);

const router = useRouter();

const enter = async () => {
  await checkAuthenticated(form);

  if (getAuthenticated.value) {
    emit("update:visible", false);
    router.push({ name: "crm" });
  } else {
    switch (getAnswerServer.value.status) {
      case true: {
        ElMessage({
          type: "error",
          dangerouslyUseHTMLString: true,
          message: getAnswerServer.value.message,
        });
        break;
      }
      case false: {
        router.push({ name: "block" });
        break;
      }
    }

    close();
  }
};

const close = () => {
  // router.push({ name: "authent" });
};

const handleClose = () => {
  ElMessage.warning("Oops, для авторизації треба ввести логін і пароль");
};

const changeKey = (event) => {
  if (["Enter", "NumpadEnter"].includes(event.code)) {
    enter();
  }
};
</script>
