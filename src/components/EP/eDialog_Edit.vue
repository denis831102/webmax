<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="handleClose"
    title="Редагування користувача"
    width="500"
  >
    <el-form :model="form">
      <el-form-item label="Ім'я" :label-width="formLabelWidth">
        <el-input v-model="form.name" autocomplete="off" />
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
        <el-button type="primary" @click="confirm"> Зберегти </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { defineProps, reactive, defineEmits, inject, onUpdated } from "vue";

const props = defineProps({
  visible: Boolean,
});

const emit = defineEmits([]);

const setting = inject("setting");

const formLabelWidth = "140px";

const form = reactive({
  name: "",
  status: "",
  date1: "",
  date2: "",
  delivery: false,
  type: [],
  resource: "",
  desc: "",
});

const handleClose = () => {
  emit("update:visible", false);
};

const confirm = () => {
  let _tab = setting.value.tables["tabUser"];
  _tab.data[_tab.numRec].PIB = form.name;

  emit("update:visible", false);
};

onUpdated(() => {
  let _tab = setting.value.tables["tabUser"];
  form.name = _tab.data[_tab.numRec].PIB;
});
</script>
