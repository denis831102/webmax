<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="handleClose"
    title="Редагування"
    width="500"
  >
    <el-form :model="form">
      <el-form-item label="Ім'я" :label-width="formLabelWidth">
        <el-input v-model="form.name" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Zones" :label-width="formLabelWidth">
        <el-select v-model="form.region" placeholder="Please select a zone">
          <el-option label="Zone No.1" value="shanghai" />
          <el-option label="Zone No.2" value="beijing" />
        </el-select>
      </el-form-item>
    </el-form>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="$emit('update:visible', false)">Cancel</el-button>
        <el-button type="primary" @click="confirm"> Confirm </el-button>
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
  region: "",
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
  emit("update:visible", false);
  setting.value.tableData[setting.value.numRec].name = form.name;
};

onUpdated(() => {
  form.name = setting.value.tableData[setting.value.numRec].name;
});
</script>
