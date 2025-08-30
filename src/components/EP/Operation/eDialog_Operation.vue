<template>
  <el-dialog
    :modelValue="props.visible"
    :title="form.title"
    :before-close="closeForm"
    :close-on-click-modal="false"
    :width="getWidth[0]"
  >
    <el-form :model="form" label-width="auto">
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
        <el-col :span="5">
          <el-date-picker
            v-model="form.date"
            type="date"
            format="DD.MM.YYYY"
            value-format="YYYY-MM-DD"
            :placeholder="getDate"
            style="width: 100%"
          />
        </el-col>

        <el-col :span="5">
          <el-tag type="info" size="large" style="font-size: 12pt">{{
            getTime
          }}</el-tag>
        </el-col>
      </el-form-item>

      <!-- :data="form.tableOperation.filter((el) => +el.isMoveKassa != -1)" -->

      <el-form-item style="box-shadow: 0px -1px 6px 2px #b0b3b7">
        <el-table
          :data="form.tableOperation.filter((el) => +el.isMoveKassa != -1)"
          :default-sort="{ prop: 'id', order: 'ascending' }"
          show-summary
          border="true"
          height="300"
          style="width: 100%; margin: 5px; font-size: 9pt"
        >
          <el-table-column label="Операція" :width="getWidth[1]" sortable>
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
                @focus="clearInp"
                style="width: 95%; height: 40px"
              />
            </template>
          </el-table-column>

          <el-table-column label="Ціна одиниці" prop="price" sortable>
            <template #default="props">
              <el-input-number
                v-if="getSettingUser.colOper == 'price'"
                v-model="props.row.price"
                :precision="3"
                :step="1"
                :min="0"
                size="small"
                @focus="clearInp"
                style="width: 95%; height: 40px"
              />
              <div v-else style="text-align: center; font-size: 14pt">
                {{ props.row.price }}
              </div>
            </template>
          </el-table-column>

          <el-table-column label="Сума" prop="summa" sortable>
            <template #default="props">
              <el-input-number
                v-if="getSettingUser.colOper == 'summa'"
                v-model="props.row.summa"
                :precision="3"
                :step="1"
                :min="0"
                size="small"
                @focus="clearInp"
                style="width: 95%; height: 40px"
              />
              <div v-else style="text-align: center; font-size: 14pt">
                {{ props.row.summa }}
              </div>
            </template>
          </el-table-column>
        </el-table>
      </el-form-item>

      <el-form-item label="Додати операцію">
        <el-col :span="3">
          <el-cascader
            v-model="selOperation"
            :options="form.options"
            :props="form.propsCascader"
            @change="addOperation"
            clearable
            collapse-tags
            collapse-tags-tooltip
            :max-collapse-tags="0"
            placeholder=" "
            style="width: 50px"
          />
        </el-col>

        <el-col :span="8" v-if="form.visibleContrAgent">
          <el-radio-group v-model="form.modeOtg" v-on:change="getSklad()">
            <el-radio-button label="склад ЦМ" value="cm" />
            <el-radio-button label="покупець" value="ca" />
          </el-radio-group>
        </el-col>

        <el-col :span="13" v-if="form.visibleContrAgent">
          <el-select v-model="form.curContragent" style="width: 100%">
            <el-option
              v-for="item in form.optionContragent"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
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
        <el-button @click="closeForm">Вийти</el-button>
        <el-button type="primary" @click="saveTransaction">Зберегти</el-button>
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
  watch,
  watchEffect,
  computed,
  onUnmounted,
  onActivated,
  onUpdated,
  onBeforeUpdate,
  onMounted,
  onBeforeMount,
} from "vue";
import { HTTP } from "@/hooks/http";
import { useStore } from "vuex";
import { ElMessage, ElMessageBox } from "element-plus";
import { Delete } from "@element-plus/icons-vue";

const props = defineProps({
  visible: Boolean,
  namePunkt: String,
  idPunkt: Number,
});
const emit = defineEmits([]);
const store = useStore();
const getCurUser = computed(() => store.getters.getCurUser);
const getSettingUser = computed(() => store.getters.getSettingUser);
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
  modeOtg: "cm",
  visibleContrAgent: false,
  optionContragent: [],
  curContragent: "",
  delOperation: [],
  addOperation: [],
  chnOperation: [],
  delOperation_child: [],
  addOperation_child: [],
  chnOperation_child: [],
});
const selOperation = ref([]);

watch(
  () => form.visibleContrAgent,
  () => getSklad()
);

watchEffect(() => {
  form.tableOperation.forEach((el) => {
    if (getSettingUser.value.colOper == "price") {
      el.summa = (el.count * el.price).toFixed(2);
    } else {
      el.price = el.count != 0 ? (el.summa / el.count).toFixed(2) : "";
    }

    const kasaEl = form.tableOperation.find(
      (fEl) => fEl.token == el.token && fEl.isMoveKassa == -1
    );
    if (kasaEl) kasaEl.count = el.summa;
  });
});

const getOperation = async () => {
  if (!setting.value.dialog["editOperation"].visible) return;

  try {
    const response = await HTTP.get("", {
      params: {
        _method: "getOperation",
        _id_U: getCurUser.value.id,
        _id_P: props.idPunkt,
      },
    });

    form.options = response.data;
    // ElMessage.success("Операції оновлені");
  } catch (e) {
    ElMessage("Помилка завантаження операцій");
  }
};

const loadOperation = (isRedactor = false) => {
  const curTransaction = setting.value.tables["tabTransaction"].curRow;

  form.tableOperation = curTransaction.listOper.map((curOper, ind) => {
    return {
      nameOperation: [curOper.name_V, curOper.name_K, curOper.name_M].join(
        " / "
      ),
      maxCount:
        +curOper.dir == -1 && curOper.id_V != 5
          ? curOper.countBits + curOper.count
          : 999999999,
      curCount: curOper.countBits,
      unit: curOper.unit,
      mode: "change",

      count: isRedactor ? curOper.count : 0,
      price: curOper.price,
      summa: isRedactor ? curOper.count * curOper.price : 0,
      old: {
        count: isRedactor ? curOper.count : 0,
        price: curOper.price,
      },

      id_T: curTransaction.id_T,
      id_O: curOper.id_O,
      id_O_child: curOper.id_O_child,
      id_V: curOper.id_V,
      id_K: curOper.id_K,
      id_M: curOper.id_M,
      id: ind,
      isMoveKassa: curOper.isMoveKassa,
      token: curOper.token,
    };
  });

  if (!isRedactor) {
    form.tableOperation = form.tableOperation.filter(
      (el) => +el.isMoveKassa != -1
    );
  }

  form.visibleContrAgent =
    curTransaction.id_Bu != 0 || curTransaction.id_cm != 0;
  form.modeOtg = curTransaction.id_Bu != 0 ? "ca" : "cm";

  if (form.visibleContrAgent) {
    if (form.modeOtg == "cm") {
      form.comment = curTransaction.comment.split(";")[1];
    }
    form.curContragent =
      curTransaction.id_Bu != 0 ? curTransaction.id_Bu : curTransaction.id_cm;
  } else {
    form.comment = curTransaction.comment;
  }

  getSklad(
    curTransaction.id_Bu > 0 ? curTransaction.id_Bu : curTransaction.id_cm
  );
};

const addOperation = () => {
  if (!selOperation.value.length) return;

  const curOper = selOperation.value[selOperation.value.length - 1];

  const isNotOtg = form.tableOperation.reduce(
    (res, el) => res | (el.id_V != 2),
    false
  );
  if (curOper[0].id == 2 && isNotOtg) {
    ElMessage.error(
      `Операцію "Відвантаження" треба створювати в окремій транзакції.`
    );
    return;
  }
  if (curOper[0].id != 2 && form.visibleContrAgent) {
    ElMessage.error(
      `В транзакцію з відвантаженням заборонено додавати інші операції. Операцію "${
        form.options[curOper[0].num].label
      }" треба створювати в окремій транзакції.`
    );
    return;
  }

  const newOperation = {
    nameOperation: [
      form.options[curOper[0].num].label,
      form.options[curOper[0].num].children[[curOper[1].num]].label,
      form.options[curOper[0].num].children[[curOper[1].num]].children[
        [curOper[2].num]
      ].label,
    ].join(" / "),
    maxCount:
      +curOper[2].dir == -1 && curOper[0].id != 5
        ? form.options[curOper[0].num].children[[curOper[1].num]].children[
            [curOper[2].num]
          ].value.count
        : 999999999,
    curCount:
      form.options[curOper[0].num].children[[curOper[1].num]].children[
        [curOper[2].num]
      ].value.count,
    unit: curOper[2].unit,
    mode: "add",

    count: 0,
    price: 1,
    summa: 0,

    id_V: curOper[0].id,
    id_K: curOper[1].id,
    id_M: curOper[2].id,
    token: Math.random(),
  };
  // form.tableOperation = [...form.tableOperation, ...newOperation];
  form.tableOperation.push(newOperation);
  checkVidOper();
};

const delOperation = (row) => {
  form.tableOperation = form.tableOperation.filter((el) => {
    const isAdd =
      (el.id_V != row.id_V || el.id_M != row.id_M) && el.token != row.token;

    if (!isAdd && el.mode == "change") {
      form.delOperation.push({
        id_O: el.id_O,
        id_M: el.id_M,
        id_V: el.id_V,
        d_count: el.count,
        d_price: el.price,
        old_count: 0,
        old_price: 0,
        new_count: el.count,
        new_price: el.price,
        is_move_kassa: el.isMoveKassa,
      });

      if (oper.id_V == 2) {
        form.delOperation_child.push({
          id_O: el.id_O_child,
          id_M: el.id_M,
          id_V: 1,
          d_count: el.count,
          d_price: el.price,
          old_count: 0,
          old_price: 0,
          new_count: el.count,
          new_price: el.price,
          is_move_kassa: 0,
        });
      }
    }

    return isAdd;
  });

  selOperation.value = [];
  checkVidOper();
};

const checkVidOper = () => {
  // let vid = false;
  // form.tableOperation.forEach((el) => {
  //   vid |= el.id_V == 2;
  // });
  form.visibleContrAgent = form.tableOperation.reduce(
    (res, el) => res | (el.id_V == 2),
    false
  );
};

const saveTransaction = () => {
  // const countDay = daysBetween(form.date, form.curDate);
  // const limitDay = 3;

  // if (countDay > limitDay) {
  if (!checkMonth(form.date, form.curDate)) {
    ElMessage.error(
      `Транзакція не збережена. Правити дозволено в поточному місяці `
    );
  } else {
    form.isSave ? addTransaction() : changeTransaction();
  }
};

const addTransaction = async () => {
  try {
    // const groupOperation = form.tableOperation.map((oper) => {
    //   return {
    //     id_V: oper.id_V,
    //     id_M: oper.id_M,
    //     d_count: oper.count,
    //     d_price: oper.price,
    //     old_count: 0,
    //     old_price: 0,
    //     new_count: oper.count,
    //     new_price: oper.price,
    //     mode_otg: form.visibleContrAgent ? form.modeOtg : "",
    //     id_agent: form.visibleContrAgent ? form.curContragent : 0,
    //   };
    // });

    let idTChild = 0,
      nameContragent = "";
    const groupOperation = [];
    const groupOperationChild = [];
    const token = generateToken(4);

    form.tableOperation.forEach((oper) => {
      groupOperation.push({
        id_V: oper.id_V,
        id_M: oper.id_M,
        d_count: oper.count,
        d_price: oper.price,
        old_count: 0,
        old_price: 0,
        new_count: oper.count,
        new_price: oper.price,
        mode_otg: form.visibleContrAgent && oper.id_V == 2 ? form.modeOtg : "",
        id_agent:
          form.visibleContrAgent && oper.id_V == 2 ? form.curContragent : 0,
        is_move_kassa: 1,
        token,
      });

      if (oper.id_V == 2 && form.modeOtg == "cm") {
        groupOperationChild.push({
          id_V: 1,
          id_M: oper.id_M,
          d_count: oper.count,
          d_price: oper.price,
          old_count: 0,
          old_price: 0,
          new_count: oper.count,
          new_price: oper.price,
          mode_otg: "",
          id_agent: 0,
          is_move_kassa: 0,
          token,
        });
      }
    });

    if (groupOperationChild.length) {
      const responseChild = await HTTP.post("", {
        _method: "addTransaction",
        _idUser: getCurUser.value.id,
        _idPunkt: form.curContragent,
        _date: form.date,
        _time: getTime.value,
        _comment: `переміщення з ${form.namePunkt.trim()}; ${form.comment}`,
        _idTChild: 0,
        _isEdit: 0,
        _isDel: 0,
        _opers: groupOperationChild,
      });
      idTChild = responseChild.data.idT;
      nameContragent = form.optionContragent.find(
        (el) => el.id == form.curContragent
      ).name;
    }

    const response = await HTTP.post("", {
      _method: "addTransaction",
      _idUser: getCurUser.value.id,
      _idPunkt: props.idPunkt,
      _date: form.date,
      _time: getTime.value,
      _comment: [
        groupOperationChild.length ? `відвантаження на ${nameContragent};` : "",
        form.comment,
      ]
        .join(" ")
        .trim(),
      _idTChild: idTChild,
      _isEdit: 1, // form.visibleContrAgent && form.modeOtg == "cm" ? 0 : 1,
      _isDel: 1,
      _opers: groupOperation,
    });

    if (response.data.isSuccesfull) {
      emit("update:visible", false);
      if (+getSettingUser.value.isShowMes) {
        ElMessage.success(response.data.message);
      }
    } else {
      ElMessageBox({
        title: "Увага!",
        type: "error",
        message: response.data.message,
      });
    }
  } catch (e) {
    ElMessage.error("Помилка збереження транзакції");
  }
};

const changeTransaction = async () => {
  try {
    form.addOperation = [];
    form.addOperation_child = [];
    form.chnOperation = [];
    form.chnOperation_child = [];
    let id_T = 0;
    let id_T_child = 0;

    const nameContragent = form.optionContragent.find(
      (el) => el.id == form.curContragent
    ).name;
    const token = generateToken(4);

    form.tableOperation.forEach((oper) => {
      switch (oper.mode) {
        case "add": {
          form.addOperation.push({
            id_V: oper.id_V,
            id_M: oper.id_M,
            d_count: oper.count,
            d_price: oper.price,
            old_count: 0,
            old_price: 0,
            new_count: oper.count,
            new_price: oper.price,
            mode_otg:
              form.visibleContrAgent && oper.id_V == 2 ? form.modeOtg : "",
            id_agent:
              form.visibleContrAgent && oper.id_V == 2 ? form.curContragent : 0,
            is_move_kassa: 1,
            token,
          });

          if (oper.id_V == 2) {
            form.addOperation_child.push({
              id_V: 1,
              id_M: oper.id_M,
              d_count: oper.count,
              d_price: oper.price,
              old_count: 0,
              old_price: 0,
              new_count: oper.count,
              new_price: oper.price,
              mode_otg: "",
              id_agent: 0,
              is_move_kassa: 0,
              token,
            });
          }
          break;
        }
        case "change": {
          let dCount = oper.count - oper.old.count,
            dPrice = oper.price - oper.old.price;

          id_T = oper.id_T;
          id_T_child = oper.id_T_child;

          if (dCount != 0 || dPrice != 0) {
            form.chnOperation.push({
              id_O: oper.id_O,
              id_V: oper.id_V,
              id_M: oper.id_M,
              d_count: dCount,
              d_price: dPrice,
              old_count: oper.old.count,
              old_price: oper.old.price,
              new_count: oper.count,
              new_price: oper.price,
              mode_otg:
                form.visibleContrAgent && oper.id_V == 2 ? form.modeOtg : "",
              id_agent:
                form.visibleContrAgent && oper.id_V == 2
                  ? form.curContragent
                  : 0,
              is_move_kassa: 1,
            });
          }

          if (oper.id_V == 2) {
            form.chnOperation_child.push({
              id_O: oper.id_O_child,
              id_V: 1,
              id_M: oper.id_M,
              d_count: dCount,
              d_price: dPrice,
              old_count: oper.old.count,
              old_price: oper.old.price,
              new_count: oper.count,
              new_price: oper.price,
              mode_otg: "",
              id_agent: 0,
              is_move_kassa: 0,
            });
          }
          break;
        }
      }
    });

    if (form.visibleContrAgent) {
      const responseChild = await HTTP.post("", {
        _method: "changeTransaction",
        // _idUser: getCurUser.value.id,
        // _idPunkt: props.idPunkt,
        _id_T: id_T_child,
        _date: form.date,
        _time: getTime.value,
        _comment: `переміщення з ${form.namePunkt.trim()}; ${form.comment}`,
        _opersDel: form.delOperation_child,
        _opersAdd: form.addOperation_child,
        _opersChn: form.chnOperation_child,
      });
    }

    const response = await HTTP.post("", {
      _method: "changeTransaction",
      _idUser: getCurUser.value.id,
      _idPunkt: props.idPunkt,
      _id_T: id_T,
      _date: form.date,
      _time: getTime.value,
      _comment: [
        form.visibleContrAgent && form.modeOtg == "cm"
          ? `відвантаження на ${nameContragent};`
          : "",
        form.comment,
      ]
        .join(" ")
        .trim(),
      _opersDel: form.delOperation,
      _opersAdd: form.addOperation,
      _opersChn: form.chnOperation,
    });

    if (response.data.isSuccesfull) {
      emit("update:visible", false);
      if (+getSettingUser.value.isShowMes) {
        ElMessage.success(response.data.message);
      }
    } else {
      ElMessage.error(response.data.message);
    }
  } catch (e) {
    ElMessage.error("Помилка редагування транзакції");
  }
};

const closeForm = () => {
  form.delOperation = [];
  form.addOperation = [];
  form.chnOperation = [];
  form.tableOperation = [];
  form.visibleContrAgent = false;
  emit("update:visible", false);
};

const clearInp = (event) => {
  if (+event.target.value == 0) event.target.value = "";
};

const clearForm = () => {
  form.tableOperation = [];
  selOperation.value = [];
  form.comment = "";
  form.visibleContrAgent = false;
};

const getWidth = computed(() => {
  return [
    setting.value.displaySize == "large" ? "800px" : "600px",
    setting.value.displaySize == "large" ? "200px" : "180px",
  ];
});

const getDate = computed(() => {
  if (!form.date || typeof form.date != "object") return "";
  const date = {
    d: form.date.getDate(),
    m: form.date.getMonth() + 1,
    y: form.date.getFullYear(),
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

const daysBetween = (date1, date2) => {
  // Преобразуем в объекты Date
  const d1 = new Date(date1);
  const d2 = new Date(date2);

  // Разница в миллисекундах
  const diffTime = Math.abs(d2 - d1);

  // Переводим в дни (1000 мс * 60 сек * 60 мин * 24 ч)
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

const checkMonth = (date1, date2) => {
  // Преобразуем в объекты Date
  const d1 = new Date(date1);
  const d2 = new Date(date2);
  return d1.getMonth() == d2.getMonth();
};

const startTimer = () => {
  form.timer = setInterval(() => {
    form.curDate = new Date();
  }, 1000);
};

const getSklad = async (id_agent = 0) => {
  if (!form.visibleContrAgent) return;

  switch (form.modeOtg) {
    case "cm": {
      const response = await HTTP.get("", {
        params: {
          _method: "getPunkt",
        },
      });
      form.optionContragent = response.data.filter((el) => {
        return el.typeP == "cm";
      });
      break;
    }
    case "ca": {
      const response = await HTTP.get("", {
        params: {
          _method: "getBuyer",
        },
      });
      form.optionContragent = response.data;
      break;
    }
  }
  form.curContragent = id_agent == 0 ? form.optionContragent[0].id : id_agent;
};

const generateToken = (length = 12) => {
  const lower = "abcdefghijklmnopqrstuvwxyz";
  const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  const digits = "0123456789";
  const special = "!@#$%^&*()-_=+[]{};:,.<>?";
  const all = `${lower}${upper}${digits}`;

  let password = "";

  // гарантируем по одному символу из каждой группы
  // password +=
  //   lower[
  //     Math.floor(crypto.getRandomValues(new Uint32Array(1))[0] % lower.length)
  //   ];
  // password +=
  //   upper[
  //     Math.floor(crypto.getRandomValues(new Uint32Array(1))[0] % upper.length)
  //   ];
  // password +=
  //   digits[
  //     Math.floor(crypto.getRandomValues(new Uint32Array(1))[0] % digits.length)
  //   ];
  // password +=
  //   special[
  //     Math.floor(crypto.getRandomValues(new Uint32Array(1))[0] % special.length)
  //   ];

  // добираем оставшиеся символы
  for (let i = 0; i < length; i++) {
    const idx = crypto.getRandomValues(new Uint32Array(1))[0] % all.length;
    password += all[idx];
  }

  // перемешиваем символы
  return password
    .split("")
    .sort(() => Math.random() - 0.5)
    .join("");
};

onUpdated(async () => {
  form.namePunkt = props.namePunkt;
  await getOperation();
  startTimer();

  switch (setting.value.dialog["editOperation"].initiator) {
    case "createOperation": {
      form.title = "Створення транзакції операцій";
      form.tableOperation = [];
      selOperation.value = [];
      form.comment = "";
      form.isSave = true;
      form.date = form.curDate;
      break;
    }
    case "copyOperation": {
      form.title = "Дублювання транзакції операцій";
      const _tab = setting.value.tables["tabTransaction"];

      form.tableOperation = [];
      form.isSave = true;
      form.date = form.curDate;
      // selOperation.value = JSON.parse(_tab.curRow.groupOperation);
      loadOperation();
      break;
    }
    case "editOperation": {
      form.title = "Редагування транзакції операцій";
      const _tab = setting.value.tables["tabTransaction"];
      const arDate = _tab.curRow.date.split(".");

      form.tableOperation = [];
      form.delOperation = [];
      form.isSave = false;
      form.date = new Date(`${arDate[2]}-${arDate[1]}-${arDate[0]}`);
      // selOperation.value = JSON.parse(_tab.curRow.groupOperation);
      loadOperation(true);
      break;
    }
  }
});

onActivated(async () => {});

onUnmounted(() => {
  clearInterval(form.timer);
});
</script>

<style></style>
