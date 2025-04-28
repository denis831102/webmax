<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="closeWindow"
    :title="form.title"
    width="500"
  >
    <el-form :model="form">
      <el-form-item label="Категорія" :label-width="formLabelWidth">
        <el-select v-model="form.kat">
          <el-option
            v-for="item in selKat"
            :key="item.id_K"
            :label="item.name_K"
            :value="item.name_K"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="Номенклатура" :label-width="formLabelWidth">
        <el-input v-model="form.name" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Одиниця виміру" :label-width="formLabelWidth">
        <el-input v-model="form.unit" autocomplete="off" />
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
  computed,
  onUpdated,
} from "vue";
import { HTTP } from "@/hooks/http";
import { ElMessage } from "element-plus";

const props = defineProps({
  visible: Boolean,
});
const emit = defineEmits([]);
const setting = inject("setting");
const formLabelWidth = "140px";
const form = reactive({
  title: "",
  name: "",
  kat: "",
  unit: "",
});
// const store = useStore();
// const setCurUser = (user) => store.commit("setCurUser", user);
// const getCurUser = computed(() => store.getters.getCurUser);

const getKategories = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getKategories",
      },
    });
    setting.value.tables["tabKategories"].data = response.data;
    ElMessage.success("Категорії оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження...");
  }
};

const closeWindow = () => {
  emit("update:visible", false);
};

const save = async () => {
  switch (setting.value.dialog["editMaterial"].initiator) {
    case "table_Material_edit": {
      const _tab = setting.value.tables["tabMaterial"];
      const id_K = setting.value.tables["tabKategories"].data.find(
        (el) => el.name_K == form.kat
      ).id_K;
      const response = await HTTP.get("", {
        params: {
          _method: "setMaterial",
          _id_M: _tab.curRow.id_M,
          _name_M: form.name,
          _id_K: id_K,
          _unit: form.unit,
        },
      });

      if (response.data.isSuccesfull) {
        const material = _tab.data.find((el) => el.id_M == _tab.curRow.id_M);
        [
          material.name_M,
          material.name_K,
          material.id_M,
          material.id_K,
          material.unit,
        ] = [form.name, form.kat, _tab.curRow.id_M, id_K, form.unit];
        emit("update:visible", false);
      } else {
        ElMessage.error("Дані не змінені");
      }
      break;
    }
    case "table_Material_copy_add":
    case "table_Material_add": {
      const _tab = setting.value.tables["tabKategories"];
      const id_K = _tab.data.find((el) => el.name_K == form.kat).id_K;
      const response = await HTTP.get("", {
        params: {
          _method: "addMaterial",
          _name_M: form.name,
          _id_K: id_K,
          _unit: form.unit,
        },
      });

      if (response.data.isSuccesfull) {
        const _tab = setting.value.tables["tabMaterial"];
        const material = {
          id_K: id_K,
          id_M: response.data.id_M,
          name_K: form.kat,
          name_M: form.name,
          unit: form.unit,
        };

        _tab.data.push(material);
        ElMessage.success("Новий матеріал додано");
        emit("update:visible", false);
      } else {
        ElMessage.error("Матеріал не додано");
      }
      break;
    }
  }
};

const selKat = computed(() => {
  return setting.value.tables["tabKategories"].data;
});

onUpdated(async () => {
  const _tab = setting.value.tables["tabMaterial"];
  await getKategories();

  switch (setting.value.dialog["editMaterial"].initiator) {
    case "table_Material_edit": {
      form.title = "Редагування Номенклатури";

      [form.name, form.kat, form.unit] = [
        _tab.curRow.name_M,
        _tab.curRow.name_K,
        _tab.curRow.unit,
      ];
      break;
    }
    case "table_Material_copy_add": {
      form.title = "Створення нового за прикладом";
      [form.name, form.kat, form.unit] = [
        _tab.curRow.name_M,
        _tab.curRow.name_K,
        _tab.curRow.unit,
      ];

      break;
    }
    case "table_Material_add": {
      form.title = "Додавання номенклатури";
      form.name = "";
      form.kat = setting.value.tables["tabKategories"].data[1].name_K;
      form.unit = "";
      break;
    }
  }
});
</script>
