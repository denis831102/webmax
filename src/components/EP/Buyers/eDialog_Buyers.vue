<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="closeWindow"
    :title="form.title"
    width="500"
  >
    <el-form :model="form">
      <el-form-item label="Назва Покупця" :label-width="formLabelWidth">
        <el-input v-model="form.name" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Код 1С" :label-width="formLabelWidth">
        <el-input v-model="form.cod" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Коментар" :label-width="formLabelWidth">
        <el-input v-model="form.commit"> </el-input>
      </el-form-item>
      <el-form-item label="Тип Розрахунку" :label-width="formLabelWidth">
        <el-radio-group v-model="form.typeM" size="large" fill="#6cf">
          <el-radio-button label="КЕШ" value="cash" />
          <el-radio-button label="Б-Г" value="bank" />
        </el-radio-group>
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
import { defineProps, reactive, defineEmits, inject, onUpdated } from "vue";
import { HTTP } from "@/hooks/http";
import { ElMessage } from "element-plus";
// import { useStore } from "vuex";

const props = defineProps({
  visible: Boolean,
});
const emit = defineEmits([]);
const setting = inject("setting");
const formLabelWidth = "140px";
const form = reactive({
  title: "",
  name: "",
  cod: "",
  commit: "",
  typeM: "cash",
});

const closeWindow = () => {
  emit("update:visible", false);
};

const save = async () => {
  switch (setting.value.dialog["editBuyers"].initiator) {
    case "table_Buyer_edit": {
      const _tab = setting.value.tables["tabBuyers"];

      const response = await HTTP.get("", {
        params: {
          _method: "setBuyer",
          _id: _tab.curRow.id,
          _name: form.name,
          _cod: form.cod,
          _commit: form.commit,
          _typeM: form.typeM,
        },
      });

      if (response.data.isSuccesfull) {
        const buyer = _tab.data.find((el) => el.id == _tab.curRow.id);
        [buyer.name, buyer.cod, buyer.commit, buyer.typeM] = [
          form.name,
          form.cod,
          form.commit,
          form.typeM,
        ];
        emit("update:visible", false);
      } else {
        ElMessage.error("Дані не змінені");
      }
      break;
    }
    case "table_Buyer_add": {
      const response = await HTTP.get("", {
        params: {
          _method: "addBuyer",
          _name: form.name,
          _cod: form.cod,
          _commit: form.commit,
          _typeM: form.typeM,
        },
      });

      if (response.data.isSuccesfull) {
        const _tab = setting.value.tables["tabBuyers"];
        const buyer = {
          id: response.data.id,
          name: response.data.name,
          cod: response.data.cod,
          commit: form.commit,
          typeM: form.typeM,
        };

        _tab.data.push(buyer);
        ElMessage.success("Нового покупця додано");
        emit("update:visible", false);
      } else {
        ElMessage.error("Нового покупця не додано");
      }
      break;
    }
  }
};

onUpdated(async () => {
  switch (setting.value.dialog["editBuyers"].initiator) {
    case "table_Buyer_edit": {
      form.title = "Редагування Покупця";
      const _tab = setting.value.tables["tabBuyers"];

      const response = await HTTP.get("", {
        params: {
          _method: "getBuyer",
        },
      });
      setting.value.tables["tabBuyers"].data = response.data;

      [form.name, form.cod, form.commit, form.typeM] = [
        _tab.curRow.name,
        _tab.curRow.cod,
        _tab.curRow.commit,
        _tab.curRow.typeM,
      ];
      break;
    }
    case "table_Buyer_add": {
      form.title = "Додавання Покупця";

      const response = await HTTP.get("", {
        params: {
          _method: "getBuyer",
        },
      });
      setting.value.tables["tabBuyers"].data = response.data;

      form.name = "";
      form.cod = "";
      form.commit = "";
      form.typeM = "cash";

      break;
    }
  }
});
</script>
