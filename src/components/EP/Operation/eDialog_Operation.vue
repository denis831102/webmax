<template>
  <el-dialog
    :modelValue="props.visible"
    :title="form.title"
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
            format="DD.MM.YYYY"
            value-format="YYYY-MM-DD"
            :placeholder="getDate"
            style="width: 100%"
          />
        </el-col>
        <el-col :span="2" style="text-align: center">
          <span>-</span>
        </el-col>
        <el-col :span="11">
          <el-tag type="info" size="large" style="font-size: 13pt">{{
            getTime
          }}</el-tag>
        </el-col>
      </el-form-item>

      <el-form-item style="box-shadow: 0px -1px 6px 2px #b0b3b7">
        <el-table
          :data="form.tableOperation"
          show-summary
          border="true"
          height="300"
          style="width: 100%; margin: 5px; font-size: 9pt"
        >
          <el-table-column
            prop="nameOperation"
            label="Операція"
            width="190"
            sortable
          >
            <template #default="props">
              <div>
                {{ props.row.nameOperation }}
                <span
                  style="padding: 5px; background: #c6e2ff69; font-weight: bold"
                >
                  {{ props.row.curCount }} {{ props.row.unit }}
                </span>
                <el-button
                  type="danger"
                  :icon="Delete"
                  circle
                  style="width: 10px; height: 10px"
                  @click="delOperation(props.row)"
                />
              </div>
            </template>
          </el-table-column>

          <el-table-column label="Кількість" prop="count" sortable>
            <template #default="props">
              <el-input-number
                v-model="props.row.count"
                :precision="3"
                :step="1"
                :max="props.row.maxCount"
                :min="0"
                size="small"
                style="width: 95%; height: 40px"
              />
            </template>
          </el-table-column>

          <el-table-column label="Ціна одиниці" prop="price" sortable>
            <template #default="props">
              <el-input-number
                v-model="props.row.price"
                :precision="3"
                :step="1"
                :min="0"
                size="small"
                style="width: 95%; height: 40px"
              />
            </template>
          </el-table-column>

          <el-table-column label="Сума" prop="summa" width="80" sortable>
            <template #default="props">
              {{ props.row.summa }}
            </template>
          </el-table-column>
        </el-table>
      </el-form-item>

      <el-form-item label="Додати операцію">
        <el-col :span="24">
          <el-cascader
            v-model="selOperation"
            :options="form.options"
            :props="form.propsCascader"
            @change="addOperation"
            clearable
            collapse-tags
            collapse-tags-tooltip
            :max-collapse-tags="2"
            placeholder="зробіть вибір..."
            style="width: 100%"
          />
        </el-col>
      </el-form-item>

      <el-form-item label="Коментар">
        <el-input v-model="form.comment" type="textarea" />
      </el-form-item>
    </el-form>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="clearForm" :disabled="!form.isSave"
          >Очистити</el-button
        >
        <el-button @click="$emit('update:visible', false)">Вийти</el-button>
        <el-button
          type="primary"
          @click="addTransaction"
          :disabled="!form.isSave"
          >Зберегти</el-button
        >
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
/* eslint-disable */

import {
  inject,
  reactive,
  defineProps,
  defineEmits,
  ref,
  watchEffect,
  computed,
  onUnmounted,
  onActivated,
  onUpdated,
} from "vue";
import { HTTP } from "@/hooks/http";
import { useStore } from "vuex";
import { ElMessage } from "element-plus";
import { Delete } from "@element-plus/icons-vue";

const props = defineProps({
  visible: Boolean,
  namePunkt: String,
  idPunkt: Number,
});
const emit = defineEmits([]);
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const setting = inject("setting");
const form = reactive({
  nameUser: getCurUser.value.PIB,
  namePunkt: "",
  date: "",
  comment: "",
  options: [],
  timer: {},
  curDate: new Date(),
  tableOperation: [],
  propsCascader: { multiple: true, expandTrigger: "hover" },
  title: "",
  isSave: true,
});
const selOperation = ref([]);

watchEffect(() => {
  form.tableOperation.forEach((el) => {
    el.summa = (el.count * el.price).toFixed(2);
  });
});

const addTransaction = async () => {
  try {
    const groupOperation = form.tableOperation.map((oper) => {
      return {
        id_V: oper.id_V,
        id_M: oper.id_M,
        count: oper.count,
        price: oper.price,
      };
    });

    const response = await HTTP.post("", {
      _method: "addTransaction",
      _idUser: getCurUser.value.id,
      _idPunkt: props.idPunkt,
      _date: form.date,
      _time: getTime.value,
      _comment: form.comment,
      _opers: groupOperation,
    });

    if (response.data.isSuccesfull) {
      emit("update:visible", false);
      ElMessage.success(response.data.message);
    } else {
      ElMessage.error(response.data.message);
    }
  } catch (e) {
    ElMessage.error("Помилка збереження операцій");
  }
};

const changeTransaction = async () => {
  try {
    const groupOperation = form.tableOperation.map((oper) => {
      return {
        id_V: oper.id_V,
        id_M: oper.id_M,
        count: oper.count,
        price: oper.price,
      };
    });

    const response = await HTTP.post("", {
      _method: "changeTransaction",
      _idUser: getCurUser.value.id,
      _idPunkt: props.idPunkt,
      _date: form.date,
      _time: getTime.value,
      _comment: form.comment,
      _opers: groupOperation,
    });

    if (response.data.isSuccesfull) {
      emit("update:visible", false);
      ElMessage.success(response.data.message);
    } else {
      ElMessage.error(response.data.message);
    }
  } catch (e) {
    ElMessage.error("Помилка збереження операцій");
  }
};

const loadOperation = (isRedactor = false) => {
  const curTransaction = setting.value.tables["tabTransaction"].curRow;

  form.tableOperation = curTransaction.listOper.map((curOper) => {
    return {
      nameOperation: [curOper.name_V, curOper.name_K, curOper.name_M].join(
        " / "
      ),
      maxCount: +curOper.dir == -1 ? curOper.count : 999999999,
      curCount: curOper.count,
      unit: curOper.unit,
      count: isRedactor ? curOper.count : 1,
      price: curOper.price,
      summa: 0,
      id_V: curOper.id_V,
      id_K: curOper.id_K,
      id_M: curOper.id_M,
    };
  });

  form.comment = curTransaction.comment;
};

const addOperation = () => {
  if (!selOperation.value.length) return;

  const curOper = selOperation.value[selOperation.value.length - 1];
  const newOperation = [
    {
      nameOperation: [
        form.options[curOper[0].num].label,
        form.options[curOper[0].num].children[[curOper[1].num]].label,
        form.options[curOper[0].num].children[[curOper[1].num]].children[
          [curOper[2].num]
        ].label,
      ].join(" / "),
      maxCount:
        +curOper[2].dir == -1
          ? form.options[curOper[0].num].children[[curOper[1].num]].children[
              [curOper[2].num]
            ].value.count
          : 999999999,
      curCount:
        form.options[curOper[0].num].children[[curOper[1].num]].children[
          [curOper[2].num]
        ].value.count,
      unit: curOper[2].unit,
      count: 1,
      price: 1,
      summa: 0,
      id_V: curOper[0].id,
      id_K: curOper[1].id,
      id_M: curOper[2].id,
    },
  ];
  form.tableOperation = [...form.tableOperation, ...newOperation];
};

const delOperation = (row) => {
  form.tableOperation = form.tableOperation.filter(
    (el) => el.nameOperation != row.nameOperation
    //(el) => el.id_V != row.id_V && el.id_K != row.id_K && el.id_M != row.id_M
  );
};

const clearForm = () => {
  form.tableOperation = [];
  selOperation.value = [];
  form.comment = "";
};

const handleClose = () => {
  emit("update:visible", false);
};

const getOperation = async () => {
  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getOperation",
        _id_U: getCurUser.value.id,
        _id_P: props.idPunkt,
      },
    });

    form.options = response.data;
    ElMessage.success("Операції оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження операцій");
  }
};

const getDate = computed(() => {
  const date = {
    d: form.curDate.getDate(),
    m: form.curDate.getMonth() + 1,
    y: form.curDate.getFullYear(),
  };
  return [
    (date.d < 10 ? "0" : "") + date.d,
    (date.m < 10 ? "0" : "") + date.m,
    date.y,
  ].join(".");
});

const getTime = computed(() => {
  const time = {
    h: form.curDate.getHours(),
    m: form.curDate.getMinutes(),
    s: form.curDate.getSeconds(),
  };
  return [
    (time.h < 10 ? "0" : "") + time.h,
    (time.m < 10 ? "0" : "") + time.m,
    (time.s < 10 ? "0" : "") + time.s,
  ].join(":");
});

const startTimer = () => {
  form.timer = setInterval(() => {
    form.curDate = new Date();
  }, 1000);
};

onUpdated(() => {
  form.namePunkt = props.namePunkt;

  switch (setting.value.dialog["editOperation"].initiator) {
    case "createOperation": {
      form.title = "Створення транзакції операцій";
      form.tableOperation = [];
      selOperation.value = [];
      form.comment = "";
      form.isSave = true;
      break;
    }
    case "copyOperation": {
      form.title = "Дублювання транзакції операцій";
      const _tab = setting.value.tables["tabTransaction"];

      form.tableOperation = [];
      form.isSave = true;
      selOperation.value = JSON.parse(_tab.curRow.groupOperation);
      loadOperation();
      break;
    }
    case "editOperation": {
      form.title = "Редагування транзакції операцій";
      const _tab = setting.value.tables["tabTransaction"];

      form.tableOperation = [];
      form.isSave = false;
      selOperation.value = JSON.parse(_tab.curRow.groupOperation);
      loadOperation(true);
      break;
    }
  }
});

onActivated(async () => {
  await getOperation();
  form.date = form.curDate;
  startTimer();
});

onUnmounted(() => {
  clearInterval(form.timer);
});
</script>
