<template>
  <el-dialog
    :modelValue="props.visible"
    :before-close="closeWindow"
    :title="form.title"
    width="500"
  >
    <el-form :model="form">
      <el-form-item label="Назва Пункту" :label-width="formLabelWidth">
        <el-input v-model="form.nameP" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Адреса" :label-width="formLabelWidth">
        <el-input v-model="form.adres" autocomplete="off" />
      </el-form-item>
      <el-form-item label="Менеджер" :label-width="formLabelWidth">
        <el-select v-model="form.pib">
          <el-option
            v-for="item in sourceTable"
            :key="item.PIB"
            :label="item.PIB"
            :value="item.PIB"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="Тип Пункту" :label-width="formLabelWidth">
        <el-radio-group v-model="form.typeP" size="large" fill="#6cf">
          <el-radio-button label="СЗ" value="sz" />
          <el-radio-button label="ЦМ" value="cm" />
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
// import { useStore } from "vuex";

const props = defineProps({
  visible: Boolean,
});
const emit = defineEmits([]);
const setting = inject("setting");
const formLabelWidth = "140px";
const form = reactive({
  title: "",
  nameP: "",
  pib: "",
  adres: "",
  typeP: "sz",
});

// const store = useStore();
// const setCurUser = (user) => store.commit("setCurUser", user);
// const getCurUser = computed(() => store.getters.getCurUser);

const closeWindow = () => {
  emit("update:visible", false);
};

const save = async () => {
  switch (setting.value.dialog["editPunkt"].initiator) {
    case "table_Punkt_edit": {
      const _tab = setting.value.tables["tabPunkt"];
      const idU = setting.value.tables["tabUser"].data.find(
        (el) => el.PIB == form.pib
      ).id;

      const response = await HTTP.get("", {
        params: {
          _method: "setPunkt",
          _id: _tab.curRow.id,
          _name: form.nameP,
          _idU: idU,
          _adres: form.adres,
          _typeP: form.typeP,
        },
      });

      if (response.data.isSuccesfull) {
        const punkt = _tab.data.find((el) => el.id == _tab.curRow.id);
        [punkt.name, punkt.adres, punkt.pib, punkt.typeP] = [
          form.nameP,
          form.adres,
          form.pib,
          form.typeP,
        ];
        emit("update:visible", false);
      } else {
        ElMessage.error("Дані не змінені");
      }
      break;
    }
    case "table_Punkt_copy_add":
    case "table_Punkt_add": {
      const idU = setting.value.tables["tabUser"].data.find(
        (el) => el.PIB == form.pib
      ).id;

      const response = await HTTP.get("", {
        params: {
          _method: "addPunkt",
          _name: form.nameP,
          _idU: idU,
          _adres: form.adres,
        },
      });

      if (response.data.isSuccesfull) {
        const _tab = setting.value.tables["tabPunkt"];
        const punkt = {
          id: response.data.idP,
          name: response.data.name,
          adres: response.data.adres,
          pib: form.pib,
        };

        _tab.data.push(punkt);
        ElMessage.success("Новий пункт прийому додано");
        emit("update:visible", false);
      } else {
        ElMessage.error("Пункт прийому не додано");
      }
      break;
    }
  }
};

const sourceTable = computed(() => {
  return setting.value.tables["tabUser"].data;
});

onUpdated(async () => {
  switch (setting.value.dialog["editPunkt"].initiator) {
    case "table_Punkt_edit": {
      form.title = "Редагування Пункту прийому";
      const _tab = setting.value.tables["tabPunkt"];

      const response = await HTTP.get("", {
        params: {
          _method: "getUsers",
        },
      });
      setting.value.tables["tabUser"].data = response.data;

      [form.nameP, form.adres, form.pib, form.typeP] = [
        _tab.curRow.name,
        _tab.curRow.adres,
        _tab.curRow.pib,
        _tab.curRow.typeP,
      ];
      break;
    }
    case "table_Punkt_copy_add": {
      form.title = "Створення нового за прикладом";
      const _tab = setting.value.tables["tabPunkt"];

      const response = await HTTP.get("", {
        params: {
          _method: "getUsers",
        },
      });
      setting.value.tables["tabUser"].data = response.data;

      [form.nameP, form.adres, form.pib, form.typeP] = [
        _tab.curRow.name,
        _tab.curRow.adres,
        _tab.curRow.pib,
        _tab.curRow.typeP,
      ];
      break;
    }
    case "table_Punkt_add": {
      form.title = "Додавання Пункту прийому";

      const response = await HTTP.get("", {
        params: {
          _method: "getUsers",
        },
      });
      setting.value.tables["tabUser"].data = response.data;

      form.nameP = "";
      form.adres = "";
      form.pib = setting.value.tables["tabUser"].data[0].PIB;
      form.typeP = "SZ";

      break;
    }
  }
});
</script>
