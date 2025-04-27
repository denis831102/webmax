<template>
  <el-dialog
    :modelValue="props.visible"
    title="Створення операції"
    width="600"
    :before-close="handleClose"
    style="max-width: 600px"
  >
    <el-form
      :model="form"
      label-width="auto"
      style="max-width: 600px; border: 0"
    >
      <el-form-item label="Пункт">
        <el-tag type="info">{{ form.namePunkt }}</el-tag>
      </el-form-item>

      <el-form-item label="Прізвище">
        <el-tag type="primary">{{ form.nameUser }}</el-tag>
      </el-form-item>

      <el-form-item label="Дата операції">
        <el-col :span="11">
          <el-date-picker
            v-model="form.date1"
            type="date"
            placeholder="Pick a date"
            style="width: 100%"
          />
        </el-col>
        <el-col :span="2" class="text-center">
          <span class="text-gray-500">-</span>
        </el-col>
        <el-col :span="11">
          <el-time-picker
            v-model="form.date2"
            placeholder="Pick a time"
            style="width: 100%"
          />
        </el-col>
      </el-form-item>

      <el-form-item label="Операція">
        <el-col :span="24">
          <el-cascader
            v-model="operation"
            :options="options"
            :props="multCascader"
            @change="handleChange"
            clearable
            placeholder="зробіть вибір..."
            style="width: 100%"
          />
        </el-col>
      </el-form-item>

      <el-form-item label="Коментар">
        <el-input v-model="form.coment" type="textarea" />
      </el-form-item>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="$emit('update:visible', false)">Вийти</el-button>
        <el-button type="primary" @click="saveData">Зберегти</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import {
  reactive,
  defineProps,
  defineEmits,
  ref,
  computed,
  onUpdated,
} from "vue";
import { HTTP } from "@/hooks/http";
import { useStore } from "vuex";
import { ElMessage } from "element-plus";

const props = defineProps({
  visible: Boolean,
  namePunkt: String,
});
const emit = defineEmits([]);
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
// const setting = inject("setting");
const operation = ref([]);
const form = reactive({
  nameUser: getCurUser.value.PIB,
  namePunkt: "",
  date1: "",
  date2: "",
  delivery: false,
  type: [],
  resource: "",
  coment: "",
});
const options = ref([]);
const multCascader = { multiple: true };

const saveData = () => {
  emit("update:visible", false);
  ElMessage.success("Дані будуть збережені");
};

const handleChange = () => {
  //console.log(value);
  console.log(operation.value);
  // ElMessage.success(operation.value);
};

const handleClose = () => {
  emit("update:visible", false);
};

const getOperation = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getOperation",
      },
    });

    options.value = response.data;
    ElMessage.success("Операції оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження операцій");
  }
};

onUpdated(async () => {
  await getOperation();
  form.namePunkt = props.namePunkt;
});
</script>
