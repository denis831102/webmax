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
      <el-form-item label="Пункт / прізвище">
        <el-col :span="15">
          <el-tag type="primary" style="width: 100%">{{
            form.namePunkt
          }}</el-tag>
        </el-col>
        <el-col :span="1" style="text-align: center">
          <span>/</span>
        </el-col>
        <el-col :span="8">
          <el-tag type="primary">{{ form.nameUser }}</el-tag>
        </el-col>
      </el-form-item>

      <el-form-item label="Дата операції">
        <el-col :span="11">
          <el-date-picker
            v-model="form.date"
            type="date"
            placeholder="Pick a date"
            style="width: 100%"
          />
        </el-col>
        <el-col :span="2" style="text-align: center">
          <span>-</span>
        </el-col>
        <el-col :span="11">
          <el-time-picker
            v-model="form.time"
            placeholder="Pick a time"
            style="width: 100%"
          />
        </el-col>
      </el-form-item>

      <el-form-item label="Операція">
        <el-col :span="24">
          <el-cascader
            v-model="selOperation"
            :options="form.options"
            :props="multCascader"
            @change="handleChange"
            clearable
            collapse-tags
            collapse-tags-tooltip
            :max-collapse-tags="2"
            placeholder="зробіть вибір..."
            style="width: 100%"
          />
        </el-col>
      </el-form-item>

      <el-form-item style="box-shadow: 0px -1px 6px 2px #b0b3b7">
        <el-table
          :data="tableOperation"
          show-summary
          border="true"
          height="350"
          style="width: 100%; margin: 5px; font-size: 9pt"
        >
          <el-table-column prop="nameOperation" label="Операція" width="190" />
          <el-table-column label="Кількість">
            <template #default="props">
              <el-input-number
                v-model="props.row.count"
                :precision="3"
                :step="0.1"
                :min="0"
                size="small"
                style="width: 95%; height: 40px"
              />
            </template>
          </el-table-column>
          <el-table-column label="Ціна одиниці">
            <template #default="props">
              <el-input-number
                v-model="props.row.price"
                :precision="3"
                :step="0.1"
                :min="0"
                size="small"
                style="width: 95%; height: 40px"
              />
            </template>
          </el-table-column>
          <el-table-column label="Сума" width="70">
            <template #default="props">
              {{ props.row.summa }}
            </template>
          </el-table-column>
        </el-table>
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
  watchEffect,
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
const selOperation = ref([]);
const form = reactive({
  nameUser: getCurUser.value.PIB,
  namePunkt: "",
  date: "",
  time: "",
  coment: "",
  options: [],
});
const multCascader = { multiple: true };
const tableOperation = ref([]);

const saveData = () => {
  emit("update:visible", false);
  ElMessage.success("Дані будуть збережені");
};

// const tableOperation = computed(() =>
//   selOperation.value.map((curOper) => {
//     return {
//       nameOperation: [
//         form.options[curOper[0].num].label,
//         form.options[curOper[0].num].children[[curOper[1].num]].label,
//         form.options[curOper[0].num].children[[curOper[1].num]].children[
//           [curOper[2].num]
//         ].label,
//       ].join(" / "),
//       count: 100,
//       price: 10,
//       summa: 0,
//     };
//   })
// );

const handleChange = () => {
  // console.log(selOperation.value);
  tableOperation.value = selOperation.value.map((curOper) => {
    return {
      nameOperation: [
        form.options[curOper[0].num].label,
        form.options[curOper[0].num].children[[curOper[1].num]].label,
        form.options[curOper[0].num].children[[curOper[1].num]].children[
          [curOper[2].num]
        ].label,
      ].join(" / "),
      count: 100,
      price: curOper[2].id,
      summa: 0,
    };
  });
};

watchEffect(() => {
  tableOperation.value.forEach((el) => {
    el.summa = (el.count * el.price).toFixed(2);
  });
});

// watch(
//   () => tableOperation.value[0].count,
//   () => {
//     tableOperation.value[0].summa = 45;
//   }
// );

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

    form.options = response.data;
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
