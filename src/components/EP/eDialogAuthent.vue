<template>
  <el-dialog
    :modelValue="props.visible"
    title="Авторизація"
    width="400"
    :before-close="handleClose"
  >
    <el-form
      :model="form"
      label-width="auto"
      style="max-width: 600px; border: 0"
    >
      <el-form-item label="Логін">
        <el-input v-model="form.login" />
      </el-form-item>
      <el-form-item label="Пароль">
        <el-input
          v-model="form.password"
          @keydown="changeKey"
          type="password"
          show-password="true"
          placeholder="please input"
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
  onMounted,
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
const fetchUsers = () => store.dispatch("fetchUsers");
const getAuthenticated = computed(() => store.getters.getAuthenticated);
//const changeAuthenticated = (val) => store.commit("changeAuthenticated", val);

const router = useRouter();

const enter = () => {
  emit("update:visible", false);

  checkAuthenticated(form);

  if (getAuthenticated.value) {
    router.push({ name: "crm" });
  } else {
    ElMessage.error("Oops, пароль не вірний");
    close();
  }
};

const close = () => {
  router.push({ name: "profile" });
};

const handleClose = () => {
  ElMessage.warning("Oops, для авторизації треба ввести логін і пароль");
};

const changeKey = (event) => {
  if (["Enter", "NumpadEnter"].includes(event.code)) {
    enter();
  }
};

onMounted(fetchUsers);
</script>
