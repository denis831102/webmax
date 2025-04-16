<template>
  <el-dialog
    :modelValue="visible"
    title="Повідомлення 555"
    width="400"
    :before-close="handleClose"
  >
    <span>{{ props.dialogText }}</span>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="$emit('update:visible', false)">Cancel</el-button>
        <el-button
          type="primary"
          @click="
            $emit('update:visible', false);
            props.funcOk(props.argFunc);
          "
        >
          OK
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";
import { ElMessageBox } from "element-plus";

const props = defineProps({
  visible: Boolean,
  dialogText: String,
  funcOk: Function,
  argFunc: Object,
});

const emit = defineEmits([]);

const handleClose = () => {
  ElMessageBox.confirm("Ви точно бажаєте закрити діалог?")
    .then(() => {
      emit("update:visible", false);
    })
    .catch(() => {
      // catch error
    });
};
</script>
